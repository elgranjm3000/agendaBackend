<?php

namespace App\Http\Controllers\Api;

use App\Models\JobDayContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class JobContactController extends BaseController
{
    /**
     * GET /api/job-contacts
     * Listar contactos del día
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobDayContact::query();

        // Filtros
        if ($request->has('id_offers')) {
            $query->where('id_offers', $request->id_offers);
        }

        if ($request->has('id_executive')) {
            $query->byExecutive($request->id_executive);
        }

        if ($request->has('id_client')) {
            $query->where('id_client', $request->id_client);
        }

        if ($request->has('id_phone')) {
            $query->where('id_phone', $request->id_phone);
        }

        if ($request->has('id_status')) {
            $query->where('id_status', $request->id_status);
        }

        if ($request->has('id_contact')) {
            $query->where('id_contact', $request->id_contact);
        }

        if ($request->has('scheduled')) {
            $query->scheduled();
        }

        if ($request->has('today')) {
            $query->today();
        }

        if ($request->has('date')) {
            $query->whereDate('scheduled_date', $request->date);
        }

        if ($request->has('date_from')) {
            $query->where('scheduled_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('scheduled_date', '<=', $request->date_to);
        }

        // Relaciones
        $query->with(['offer', 'phone', 'executive', 'user', 'status', 'contactStatus']);

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'stamp');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $contacts = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($contacts);
    }

    /**
     * POST /api/job-contacts
     * Crear un nuevo contacto
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_offers' => 'required|integer',
            'id_phone' => 'required|integer',
            'id_client' => 'required|integer',
            'id_executive' => 'required|integer',
            'id_status' => 'required|integer',
            'id_contact' => 'required|integer',
            'scheduled_date' => 'nullable|date',
            'comment' => 'nullable|string', // Nuevo campo de comentario
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $contact = JobDayContact::create($validator->validated());
        $contact->load(['offer', 'phone', 'executive', 'user', 'status', 'contactStatus']);

        return $this->successResponse($contact, 'Contact created successfully', 201);
    }

    /**
     * GET /api/job-contacts/{id}
     * Obtener un contacto específico
     */
    public function show($id): JsonResponse
    {
        $contact = JobDayContact::with(['offer', 'phone', 'executive', 'user', 'status', 'contactStatus'])
                                ->findOrFail($id);

        return $this->successResponse($contact);
    }

    /**
     * PUT /api/job-contacts/{id}
     * Actualizar un contacto
     */
    public function update(Request $request, $id): JsonResponse
    {
        $contact = JobDayContact::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'id_status' => 'sometimes|integer',
            'id_contact' => 'sometimes|integer',
            'scheduled_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $contact->update($validator->validated());
        $contact->load(['offer', 'phone', 'status', 'contactStatus']);

        return $this->successResponse($contact, 'Contact updated successfully');
    }

    /**
     * DELETE /api/job-contacts/{id}
     * Eliminar un contacto
     */
    public function destroy($id): JsonResponse
    {
        $contact = JobDayContact::findOrFail($id);
        $contact->delete();

        return $this->successResponse(null, 'Contact deleted successfully');
    }

    /**
     * GET /api/job-contacts/by-executive/{executiveId}
     * Obtener contactos de un ejecutivo específico
     */
    public function byExecutive($executiveId): JsonResponse
    {
        $contacts = JobDayContact::byExecutive($executiveId)
                                ->with(['offer', 'phone', 'status', 'contactStatus'])
                                ->orderBy('scheduled_date', 'desc')
                                ->get();

        return $this->successResponse([
            'executive_id' => $executiveId,
            'total' => $contacts->count(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * GET /api/job-contacts/today/{executiveId}
     * Obtener contactos de hoy para un ejecutivo
     */
    public function todayByExecutive($executiveId): JsonResponse
    {
        $contacts = JobDayContact::byExecutive($executiveId)
                                ->today()
                                ->with(['offer', 'phone', 'status', 'contactStatus', 'executive'])
                                ->orderBy('scheduled_date', 'asc')
                                ->get();

        return $this->successResponse([
            'date' => now()->toDateString(),
            'executive_id' => $executiveId,
            'total' => $contacts->count(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * PUT /api/job-contacts/{id}/reschedule
     * Reprogramar un contacto
     */
    public function reschedule(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scheduled_date' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $contact = JobDayContact::findOrFail($id);
        $contact->update($validator->validated());
        $contact->load(['offer', 'phone', 'status', 'contactStatus']);

        return $this->successResponse($contact, 'Contact rescheduled successfully');
    }

    /**
     * GET /api/job-contacts/week/{executiveId}
     * Obtener contactos de la semana para un ejecutivo
     */
    public function weekByExecutive($executiveId): JsonResponse
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $contacts = JobDayContact::byExecutive($executiveId)
                                ->whereBetween('scheduled_date', [$startOfWeek, $endOfWeek])
                                ->with(['offer', 'phone', 'status', 'contactStatus', 'executive'])
                                ->orderBy('scheduled_date', 'asc')
                                ->get();

        return $this->successResponse([
            'week_start' => $startOfWeek->toDateString(),
            'week_end' => $endOfWeek->toDateString(),
            'executive_id' => $executiveId,
            'total' => $contacts->count(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * GET /api/job-contacts/pending/{executiveId}
     * Obtener contactos pendientes (futuro) de un ejecutivo
     */
    public function pendingByExecutive($executiveId): JsonResponse
    {
        $contacts = JobDayContact::byExecutive($executiveId)
                                ->where('scheduled_date', '>', now())
                                ->with(['offer', 'phone', 'status', 'contactStatus', 'executive'])
                                ->orderBy('scheduled_date', 'asc')
                                ->get();

        return $this->successResponse([
            'executive_id' => $executiveId,
            'total' => $contacts->count(),
            'contacts' => $contacts,
        ]);
    }

    /**
     * GET /api/job-contacts/statistics
     * Obtener estadísticas de contactos
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = JobDayContact::query();

        if ($request->has('id_offers')) {
            $query->where('id_offers', $request->id_offers);
        }

        if ($request->has('id_executive')) {
            $query->where('id_executive', $request->id_executive);
        }

        if ($request->has('date_from')) {
            $query->where('stamp', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('stamp', '<=', $request->date_to);
        }

        $total = $query->count();
        $scheduled = (clone $query)->whereNotNull('scheduled_date')->count();
        $completed = (clone $query)->where('id_status', 3)->count(); // Asumiendo 3 = completado
        
        $byStatus = (clone $query)->selectRaw('id_status, COUNT(*) as total')
                                   ->groupBy('id_status')
                                   ->with('status:id_status,descrip')
                                   ->get();

        $byContactType = (clone $query)->selectRaw('id_contact, COUNT(*) as total')
                                        ->groupBy('id_contact')
                                        ->with('contactStatus:id_contact,descrip')
                                        ->get();

        return $this->successResponse([
            'total_contacts' => $total,
            'scheduled_contacts' => $scheduled,
            'completed_contacts' => $completed,
            'by_status' => $byStatus,
            'by_contact_type' => $byContactType,
        ]);
    }
}