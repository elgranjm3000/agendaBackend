<?php

namespace App\Http\Controllers\Api;

use App\Models\JobDayExecutive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobExecutiveController extends BaseController
{
    /**
     * GET /api/job-executives
     * Listar ejecutivos del día
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobDayExecutive::query();

        // Filtros
        if ($request->has('id_offers')) {
            $query->where('id_offers', $request->id_offers);
        }

        if ($request->has('id_office')) {
            $query->byOffice($request->id_office);
        }

        if ($request->has('id_status')) {
            $query->byStatus($request->id_status);
        }

        if ($request->has('id_executive')) {
            $query->where('id_executive', $request->id_executive);
        }

        if ($request->has('id_client')) {
            $query->where('id_client', $request->id_client);
        }

        if ($request->has('scheduled')) {
            $query->scheduled();
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name1', 'like', "%{$search}%")
                  ->orWhere('last_name2', 'like', "%{$search}%")
                  ->orWhere('id_client', 'like', "%{$search}%");
            });
        }

        // Filtros de fecha
        if ($request->has('scheduled_from')) {
            $query->where('scheduled_date', '>=', $request->scheduled_from);
        }

        if ($request->has('scheduled_to')) {
            $query->where('scheduled_date', '<=', $request->scheduled_to);
        }

        // Relaciones
        $query->with(['status', 'contactStatus', 'offer']);

        if ($request->boolean('with_contacts')) {
            $query->with('contacts');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'stamp');
        $sortOrder = $request->get('name', 'desc');
        $query->orderBy('name', 'desc');

        $executives = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($executives);
    }

    /**
     * POST /api/job-executives
     * Crear un nuevo ejecutivo del día
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_offers' => 'required|integer',
            'id_client' => 'required|integer',
            'dv_client' => 'required|string|max:1',
            'id_executive' => 'required|integer',
            'dv_executive' => 'required|string|max:1',
            'name' => 'required|string|max:50',
            'last_name1' => 'required|string|max:50',
            'last_name2' => 'nullable|string|max:50',
            'id_office' => 'nullable|string|max:8',
            'attrib1' => 'nullable|string|max:50',
            'attrib2' => 'nullable|string|max:50',
            'attrib3' => 'nullable|string|max:50',
            'attrib4' => 'nullable|string|max:50',
            'attrib5' => 'nullable|string|max:50',
            'attrib6' => 'nullable|string|max:50',
            'attrib7' => 'nullable|string|max:50',
            'attrib8' => 'nullable|string|max:50',
            'attrib9' => 'nullable|string|max:50',
            'attrib10' => 'nullable|string|max:50',
            'attrib11' => 'nullable|string|max:50',
            'attrib12' => 'nullable|string|max:50',
            'attrib13' => 'nullable|string|max:50',
            'attrib14' => 'nullable|string|max:50',
            'attrib15' => 'nullable|string|max:50',
            'attrib16' => 'nullable|string|max:50',
            'attrib17' => 'nullable|string|max:50',
            'attrib18' => 'nullable|string|max:50',
            'attrib19' => 'nullable|string|max:50',
            'attrib20' => 'nullable|string|max:50',
            'attrib21' => 'nullable|string|max:50',
            'attrib22' => 'nullable|string|max:50',
            'id_status' => 'nullable|integer',
            'id_contact' => 'nullable|integer',
            'scheduled_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['id_status'] = $data['id_status'] ?? 1;
        $data['id_contact'] = $data['id_contact'] ?? 0;

        $executive = JobDayExecutive::create($data);
        $executive->load(['status', 'contactStatus', 'offer']);

        return $this->successResponse($executive, 'Executive created successfully', 201);
    }

    /**
     * GET /api/job-executives/{id}
     * Obtener un ejecutivo específico
     */
    public function show($id): JsonResponse
    {
        $executive = JobDayExecutive::with(['status', 'contactStatus', 'offer', 'contacts'])
                                   ->withCount('contacts')
                                   ->findOrFail($id);

        return $this->successResponse($executive);
    }

    /**
     * PUT /api/job-executives/{id}
     * Actualizar un ejecutivo
     */
    public function update(Request $request, $id): JsonResponse
    {
        $executive = JobDayExecutive::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:50',
            'last_name1' => 'sometimes|string|max:50',
            'last_name2' => 'sometimes|string|max:50',
            'id_office' => 'sometimes|string|max:8',
            'id_status' => 'sometimes|integer',
            'id_contact' => 'sometimes|integer',
            'scheduled_date' => 'nullable|date',
            'attrib1' => 'nullable|string|max:50',
            'attrib2' => 'nullable|string|max:50',
            'attrib3' => 'nullable|string|max:50',
            'attrib4' => 'nullable|string|max:50',
            'attrib5' => 'nullable|string|max:50',
            'attrib6' => 'nullable|string|max:50',
            'attrib7' => 'nullable|string|max:50',
            'attrib8' => 'nullable|string|max:50',
            'attrib9' => 'nullable|string|max:50',
            'attrib10' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $executive->update($validator->validated());
        $executive->load(['status', 'contactStatus', 'offer']);

        return $this->successResponse($executive, 'Executive updated successfully');
    }

    /**
     * DELETE /api/job-executives/{id}
     * Eliminar un ejecutivo
     */
    public function destroy($id): JsonResponse
    {
        $executive = JobDayExecutive::findOrFail($id);
        $executive->delete();

        return $this->successResponse(null, 'Executive deleted successfully');
    }

    /**
     * PUT /api/job-executives/{id}/status
     * Actualizar el estado de un ejecutivo
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_status' => 'required|integer',
            'id_contact' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $executive = JobDayExecutive::findOrFail($id);
        $executive->update($validator->validated());
        $executive->load(['status', 'contactStatus']);

        return $this->successResponse($executive, 'Status updated successfully');
    }

    /**
     * PUT /api/job-executives/{id}/schedule
     * Programar una cita para un ejecutivo
     */
    public function schedule(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'scheduled_date' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $executive = JobDayExecutive::findOrFail($id);
        $executive->update($validator->validated());

        return $this->successResponse($executive, 'Executive scheduled successfully');
    }

    /**
     * GET /api/job-executives/by-office/{officeId}
     * Obtener ejecutivos por oficina
     */
    public function byOffice($officeId): JsonResponse
    {
        $executives = JobDayExecutive::byOffice($officeId)
                                    ->with(['status', 'contactStatus'])
                                    ->orderBy('stamp', 'desc')
                                    ->get();

        return $this->successResponse([
            'office_id' => $officeId,
            'total' => $executives->count(),
            'executives' => $executives,
        ]);
    }

    /**
     * POST /api/job-executives/bulk-update-status
     * Actualizar estado de múltiples ejecutivos
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'id_status' => 'required|integer',
            'id_contact' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $updateData = [
            'id_status' => $request->id_status,
        ];

        if ($request->has('id_contact')) {
            $updateData['id_contact'] = $request->id_contact;
        }

        $updated = JobDayExecutive::whereIn('id', $request->ids)->update($updateData);

        return $this->successResponse([
            'updated_count' => $updated,
            'ids' => $request->ids,
        ], 'Executives updated successfully');
    }
}