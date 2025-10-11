<?php

namespace App\Http\Controllers\Api;

use App\Models\JobClientStatus;
use App\Models\JobClientStatusContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobStatusController extends BaseController
{
    // ========== CLIENT STATUS ENDPOINTS ==========

    /**
     * GET /api/job-status/client
     * Listar estados de cliente
     */
    public function indexClientStatus(Request $request): JsonResponse
    {
        $query = JobClientStatus::query();

        if ($request->has('is_life')) {
            $query->where('is_life', $request->boolean('is_life'));
        }

        if ($request->has('active')) {
            $query->active();
        }

        if ($request->has('search')) {
            $query->where('descrip', 'like', "%{$request->search}%");
        }

        if ($request->has('id_status')) {
            $query->where('id_status', '=', "{$request->id_status}");
        }

        if ($request->boolean('with_counts')) {
            $query->withCount(['executives', 'contacts', 'contactStatuses']);
        }

        if ($request->boolean('with_relations')) {
            $query->with(['contactStatuses']);
        }

        $statuses = $query->orderBy('id_status')->get();

        return $this->successResponse($statuses);
    }

    /**
     * POST /api/job-status/client
     * Crear un nuevo estado de cliente
     */
    public function storeClientStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'descrip' => 'required|string|max:100',
            'is_life' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $status = JobClientStatus::create($validator->validated());

        return $this->successResponse($status, 'Client status created successfully', 201);
    }

    /**
     * GET /api/job-status/client/{id}
     * Obtener un estado específico
     */
    public function showClientStatus($id): JsonResponse
    {
        $status = JobClientStatus::withCount(['executives', 'contacts', 'contactStatuses'])
                                ->with('contactStatuses')
                                ->findOrFail($id);

        return $this->successResponse($status);
    }

    /**
     * PUT /api/job-status/client/{id}
     * Actualizar un estado de cliente
     */
    public function updateClientStatus(Request $request, $id): JsonResponse
    {
        $status = JobClientStatus::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'descrip' => 'sometimes|string|max:100',
            'is_life' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $status->update($validator->validated());

        return $this->successResponse($status, 'Client status updated successfully');
    }

    /**
     * DELETE /api/job-status/client/{id}
     * Eliminar un estado de cliente
     */
    public function destroyClientStatus($id): JsonResponse
    {
        $status = JobClientStatus::findOrFail($id);
        
        // Verificar si tiene registros asociados
        $executivesCount = $status->executives()->count();
        $contactsCount = $status->contacts()->count();
        
        if ($executivesCount > 0 || $contactsCount > 0) {
            return $this->errorResponse(
                "Cannot delete status with {$executivesCount} executives and {$contactsCount} contacts", 
                422
            );
        }

        $status->delete();

        return $this->successResponse(null, 'Client status deleted successfully');
    }

    // ========== CONTACT STATUS ENDPOINTS ==========

    /**
     * GET /api/job-status/contact
     * Listar estados de contacto
     */
    public function indexContactStatus(Request $request): JsonResponse
    {
        $query = JobClientStatusContact::query();

        if ($request->has('id_status')) {
            $query->where('id_status', $request->id_status);
        }

        if ($request->has('is_life')) {
            $query->where('is_life', $request->boolean('is_life'));
        }

         if ($request->has('id_status')) {
            $query->where('id_status', $request->id_status);
        }

        if ($request->has('is_scheduled')) {
            $query->where('is_scheduled', $request->boolean('is_scheduled'));
        }

        if ($request->has('active')) {
            $query->active();
        }

        if ($request->has('scheduled')) {
            $query->scheduled();
        }

        if ($request->has('search')) {
            $query->where('descrip', 'like', "%{$request->search}%");
        }

        $query->with('status');

        if ($request->boolean('with_counts')) {
            $query->withCount(['executives', 'contacts']);
        }

        $statuses = $query->orderBy('id_contact')->get();

        return $this->successResponse($statuses);
    }

    /**
     * POST /api/job-status/contact
     * Crear un nuevo estado de contacto
     */
    public function storeContactStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'descrip' => 'required|string|max:100',
            'id_status' => 'required|integer|exists:job_client_status,id_status',
            'is_life' => 'required|boolean',
            'is_scheduled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $status = JobClientStatusContact::create($validator->validated());
        $status->load('status');

        return $this->successResponse($status, 'Contact status created successfully', 201);
    }

    /**
     * GET /api/job-status/contact/{id}
     * Obtener un estado de contacto específico
     */
    public function showContactStatus($id): JsonResponse
    {
        $status = JobClientStatusContact::with('status')
                                       ->withCount(['executives', 'contacts'])
                                       ->findOrFail($id);

        return $this->successResponse($status);
    }

    /**
     * PUT /api/job-status/contact/{id}
     * Actualizar un estado de contacto
     */
    public function updateContactStatus(Request $request, $id): JsonResponse
    {
        $status = JobClientStatusContact::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'descrip' => 'sometimes|string|max:100',
            'id_status' => 'sometimes|integer|exists:job_client_status,id_status',
            'is_life' => 'sometimes|boolean',
            'is_scheduled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $status->update($validator->validated());
        $status->load('status');

        return $this->successResponse($status, 'Contact status updated successfully');
    }

    /**
     * DELETE /api/job-status/contact/{id}
     * Eliminar un estado de contacto
     */
    public function destroyContactStatus($id): JsonResponse
    {
        $status = JobClientStatusContact::findOrFail($id);
        
        // Verificar si tiene registros asociados
        $executivesCount = $status->executives()->count();
        $contactsCount = $status->contacts()->count();
        
        if ($executivesCount > 0 || $contactsCount > 0) {
            return $this->errorResponse(
                "Cannot delete contact status with {$executivesCount} executives and {$contactsCount} contacts", 
                422
            );
        }

        $status->delete();

        return $this->successResponse(null, 'Contact status deleted successfully');
    }

    /**
     * GET /api/job-status/all
     * Obtener todos los estados (cliente y contacto) en un solo endpoint
     */
    public function all(): JsonResponse
    {
        $clientStatuses = JobClientStatus::active()
                                        ->withCount(['executives', 'contacts'])
                                        ->get();

        $contactStatuses = JobClientStatusContact::active()
                                                ->with('status')
                                                ->withCount(['executives', 'contacts'])
                                                ->get();

        return $this->successResponse([
            'client_statuses' => $clientStatuses,
            'contact_statuses' => $contactStatuses,
        ]);
    }
}