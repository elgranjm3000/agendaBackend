<?php

namespace App\Http\Controllers\Api;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:view-reports');
    }

    public function dailyOccupancy(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $date = $request->date;
        $query = Appointment::whereDate('start_time', $date);

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $appointments = $query->with(['client:id,name', 'service:id,name', 'user:id,name'])
            ->orderBy('start_time')
            ->get();

        $totalAppointments = $appointments->count();
        $completedAppointments = $appointments->where('status', 'completed')->count();
        $cancelledAppointments = $appointments->where('status', 'cancelled')->count();
        $noShowAppointments = $appointments->where('status', 'no_show')->count();

        // Calculate occupancy rate (assuming 8 hour workday, 30 min slots = 16 possible slots)
        $possibleSlots = 16;
        $occupancyRate = $totalAppointments > 0 ? round(($totalAppointments / $possibleSlots) * 100, 2) : 0;

        return $this->successResponse([
            'date' => $date,
            'summary' => [
                'total_appointments' => $totalAppointments,
                'completed' => $completedAppointments,
                'cancelled' => $cancelledAppointments,
                'no_show' => $noShowAppointments,
                'occupancy_rate' => $occupancyRate,
            ],
            'appointments' => $appointments,
        ]);
    }

    public function salesByDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'group_by' => 'sometimes|in:day,week,month',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        $groupBy = $request->group_by ?? 'day';

        $dateFormat = match ($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $payments = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '$dateFormat') as period"),
                DB::raw('COUNT(*) as total_payments'),
                DB::raw('SUM(amount) as total_revenue'),
                DB::raw('AVG(amount) as average_payment'),
                'method'
            )
            ->groupBy('period', 'method')
            ->orderBy('period')
            ->get();

        $summary = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->selectRaw('
                COUNT(*) as total_payments,
                SUM(amount) as total_revenue,
                AVG(amount) as average_payment,
                MIN(amount) as min_payment,
                MAX(amount) as max_payment
            ')
            ->first();

        return $this->successResponse([
            'period' => [
                'from' => $fromDate->format('Y-m-d'),
                'to' => $toDate->format('Y-m-d'),
                'group_by' => $groupBy,
            ],
            'summary' => $summary,
            'data' => $payments->groupBy('period'),
        ]);
    }

    public function frequentClients(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'sometimes|integer|min:1|max:100',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
        ]);

        $limit = $request->limit ?? 20;
        $query = Client::withCount(['appointments' => function($q) use ($request) {
            if ($request->from_date && $request->to_date) {
                $q->whereBetween('start_time', [$request->from_date, $request->to_date]);
            }
        }]);

        $clients = $query->with(['appointments' => function($q) {
                $q->with('service:id,name,price')
                  ->orderBy('start_time', 'desc')
                  ->limit(5);
            }])
            ->having('appointments_count', '>', 0)
            ->orderBy('appointments_count', 'desc')
            ->limit($limit)
            ->get();

        // Calculate total spent by each client
        $clientsWithSpending = $clients->map(function($client) {
            $totalSpent = $client->appointments->sum(function($appointment) {
                return $appointment->payment?->amount ?? 0;
            });
            
            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'total_appointments' => $client->appointments_count,
                'total_spent' => $totalSpent,
                'recent_appointments' => $client->appointments,
            ];
        });

        return $this->successResponse([
            'limit' => $limit,
            'clients' => $clientsWithSpending,
        ]);
    }

    public function monthlyOverview(Request $request): JsonResponse
    {
        $request->validate([
            'year' => 'sometimes|integer|min:2020|max:2030',
            'month' => 'sometimes|integer|min:1|max:12',
        ]);

        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Appointments summary
        $appointmentsSummary = Appointment::whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = "no_show" THEN 1 ELSE 0 END) as no_show
            ')
            ->first();

        // Revenue summary
        $revenueSummary = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('
                COUNT(*) as total_payments,
                SUM(amount) as total_revenue,
                AVG(amount) as average_payment
            ')
            ->first();

        // Daily breakdown
        $dailyBreakdown = Appointment::whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->selectRaw('
                DATE(start_time) as date,
                COUNT(*) as appointments,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $this->successResponse([
            'period' => [
                'year' => $year,
                'month' => $month,
                'start_date' => $startOfMonth->format('Y-m-d'),
                'end_date' => $endOfMonth->format('Y-m-d'),
            ],
            'appointments_summary' => $appointmentsSummary,
            'revenue_summary' => $revenueSummary,
            'daily_breakdown' => $dailyBreakdown,
        ]);
    }
}