<?php

namespace App\Http\Controllers\Api;

use App\Models\JobIndicatorExecutive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    /**
     * GET /api/dashboard
     * Obtener todos los indicadores del dashboard para el ejecutivo autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        // Obtener el período (formato: YYYYMM)
        // Si no se proporciona, usar el mes actual
        $period = $request->period ?? Carbon::now()->format('Ym');
 
        
        // Obtener el id_executive del usuario autenticado
        // Asumiendo que tienes un campo rut_number en tu tabla users
        $idExecutive = auth()->user()->rut_number ?? auth()->user()->id;

        // Obtener todos los indicadores en una sola consulta

        $indicators = JobIndicatorExecutive::where('id_executive', $idExecutive)
            ->where('period', $period)
            ->whereIn('type', [10, 11, 12, 13, 20, 21])
            ->get()
            ->keyBy('type');
        // Estructurar la respuesta
        $dashboard = [
            'period' => $period,
            'executive_id' => $idExecutive,
            'kpi_1' => $this->formatKPI($indicators->get(10)),
            'kpi_2' => $this->formatKPI($indicators->get(11)),
            'kpi_3' => $this->formatKPI($indicators->get(12)),
            'kpi_4' => $this->formatKPI($indicators->get(13)),
            'chart_dual' => $this->formatDualChart($indicators->get(20)),
            'chart_single' => $this->formatSingleChart($indicators->get(21)),
        ];

        return $this->successResponse($dashboard);
    }


    public function summary(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $period = $request->period ?? Carbon::now()->format('Ym');
        $idExecutive = auth()->user()->rut_number ?? auth()->user()->id;

        // Obtener indicadores tipo 1, 2, 3, 4
        $indicators = JobIndicatorExecutive::where('id_executive', $idExecutive)
            ->where('period', $period)
            ->whereIn('type', [1, 2, 3, 4])
            ->get()
            ->keyBy('type');

        $summary = [
            'period' => $period,
            'executive_id' => $idExecutive,
            'summary_1' => $this->formatSummaryItem($indicators->get(1)),
            'summary_2' => $this->formatSummaryItem($indicators->get(2)),
            'summary_3' => $this->formatSummaryItem($indicators->get(3)),
            'summary_4' => $this->formatSummaryItem($indicators->get(4)),
        ];

        return $this->successResponse($summary);
    }


    private function formatSummaryItem($indicator): ?array
    {
        if (!$indicator) {
            return null;
        }

        return [
            'title' => $indicator->title,
            'maskAmount' => $indicator->maskAmount,
            'footer' => $indicator->footer,
        ];
    }

    /**
     * GET /api/dashboard/kpi/10
     * Obtener KPI tipo 10
     */
    public function kpi10(Request $request): JsonResponse
    {
        return $this->getKPIByType($request, 10);
    }

    /**
     * GET /api/dashboard/kpi/11
     * Obtener KPI tipo 11
     */
    public function kpi11(Request $request): JsonResponse
    {
        return $this->getKPIByType($request, 11);
    }

    /**
     * GET /api/dashboard/kpi/12
     * Obtener KPI tipo 12
     */
    public function kpi12(Request $request): JsonResponse
    {
        return $this->getKPIByType($request, 12);
    }

    /**
     * GET /api/dashboard/kpi/13
     * Obtener KPI tipo 13
     */
    public function kpi13(Request $request): JsonResponse
    {
        return $this->getKPIByType($request, 13);
    }

    /**
     * GET /api/dashboard/chart/dual
     * Obtener gráfico dual (tipo 20)
     */
    public function chartDual(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $period = $request->period ?? Carbon::now()->format('Ym');
   
        $idExecutive = auth()->user()->rut_number ?? auth()->user()->id;

        $indicator = JobIndicatorExecutive::where('id_executive', $idExecutive)
            ->where('type', 20)
            ->where('period', $period)
            ->first();

          

        if (!$indicator) {
            return $this->errorResponse('Dual chart indicator not found', 404);
        }
        $data = [
            'title' => $indicator->title,
            'footer' => $indicator->footer,
            'y1' => $this->parseArrayField($indicator->y1),
            'y2' => $this->parseArrayField($indicator->y2),
            'x1' => $this->parseArrayField($indicator->x1),
            'x2' => $this->parseArrayField($indicator->x2),
        ];

        return $this->successResponse($data);
    }

    /**
     * GET /api/dashboard/chart/single
     * Obtener gráfico simple (tipo 21)
     */
    public function chartSingle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $period = $request->period ?? Carbon::now()->format('Ym');
        $idExecutive = auth()->user()->rut_number ?? auth()->user()->id;

        $indicator = JobIndicatorExecutive::where('id_executive', $idExecutive)
            ->where('type', 21)
            ->where('period', $period)
            ->first();

        if (!$indicator) {
            return $this->errorResponse('Single chart indicator not found', 404);
        }

        $data = [
            'title' => $indicator->title,
            'footer' => $indicator->footer,
            'y1' => $this->parseArrayField($indicator->y1),
            'x1' => $this->parseArrayField($indicator->x1),
        ];

        return $this->successResponse($data);
    }

    /**
     * GET /api/dashboard/periods
     * Obtener períodos disponibles para el ejecutivo
     */
    public function periods(): JsonResponse
    {
        $idExecutive = auth()->user()->rut_number ?? auth()->user()->id;

        $periods = JobIndicatorExecutive::where('id_executive', $idExecutive)
            ->select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period');

        return $this->successResponse([
            'total' => $periods->count(),
            'periods' => $periods,
            'current_period' => Carbon::now()->format('Ym'),
        ]);
    }

    /**
     * GET /api/dashboard/compare
     * Comparar períodos
     */
    public function compare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'periods' => 'required|array|min:2|max:12',
            'periods.*' => 'string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $idExecutive = auth()->user()->rut_number ?? auth()->user()->id;
        

        $comparison = [];
        foreach ($request->periods as $period) {
            $indicators = JobIndicatorExecutive::where('id_executive', '10368381')
                ->where('period', $period)
                ->whereIn('type', [10, 11, 12, 13])
                ->get()
                ->keyBy('type');

            $comparison[$period] = [
                'kpi_1' => $this->formatKPI($indicators->get(10)),
                'kpi_2' => $this->formatKPI($indicators->get(11)),
                'kpi_3' => $this->formatKPI($indicators->get(12)),
                'kpi_4' => $this->formatKPI($indicators->get(13)),
            ];
        }

        return $this->successResponse([
            'executive_id' => $idExecutive,
            'periods_compared' => count($request->periods),
            'comparison' => $comparison,
        ]);
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Obtener KPI por tipo
     */
    private function getKPIByType(Request $request, int $type): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|string|size:6|regex:/^\d{6}$/',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $period = $request->period ?? Carbon::now()->format('Ym');
        $idExecutive = auth()->user()->rut_number ?? auth()->user()->id;

        $indicator = JobIndicatorExecutive::where('id_executive', $idExecutive)
            ->where('type', $type)
            ->where('period', $period)
            ->first();

        if (!$indicator) {
            return $this->errorResponse("KPI type {$type} not found for period {$period}", 404);
        }

        $data = [
            'title' => $indicator->title,
            'maskAmount' => $indicator->maskAmount,
            'footer' => $indicator->footer,
            'amount' => $indicator->amount,
            'title_color' => $indicator->title_color,
        ];

        return $this->successResponse($data);
    }

    /**
     * Formatear KPI
     */
    private function formatKPI($indicator): ?array
    {
        if (!$indicator) {
            return null;
        }

        return [
            'title' => $indicator->title,
            'maskAmount' => $indicator->maskAmount,
            'footer' => $indicator->footer,
            'amount' => $indicator->amount,
            'title_color' => $indicator->title_color,
        ];
    }

    /**
     * Formatear gráfico dual
     */
    private function formatDualChart($indicator): ?array
    {
        if (!$indicator) {
            return null;
        }

        return [
            'title' => $indicator->title,
            'footer' => $indicator->footer,
            'y1' => $this->parseArrayField($indicator->y1),
            'y2' => $this->parseArrayField($indicator->y2),
            'x1' => $this->parseArrayField($indicator->x1),
            'x2' => $this->parseArrayField($indicator->x2),
        ];
    }

    /**
     * Formatear gráfico simple
     */
    private function formatSingleChart($indicator): ?array
    {
        if (!$indicator) {
            return null;
        }

        return [
            'title' => $indicator->title,
            'footer' => $indicator->footer,
            'y1' => $this->parseArrayField($indicator->y1),
            'x1' => $this->parseArrayField($indicator->x1),
        ];
    }

    private function parseArrayField($field): ?array
    {
        if (empty($field)) {
            return null;
        }

        // Si ya es un array, devolverlo
        if (is_array($field)) {
            return $field;
        }

        // Intentar decodificar como JSON primero
        $decoded = json_decode($field, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Si no es JSON, separar por comas y convertir a números
        $values = array_map('trim', explode(',', $field));
        
        // Convertir strings numéricos a números
        return array_map(function($value) {
            // Si es numérico, convertir a número
            if (is_numeric($value)) {
                return strpos($value, '.') !== false 
                    ? (float) $value 
                    : (int) $value;
            }
            return $value;
        }, $values);
    }

    
}