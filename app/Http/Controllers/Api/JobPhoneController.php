<?php

namespace App\Http\Controllers\Api;

use App\Models\JobPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobPhoneController extends BaseController
{
    /**
     * GET /api/job-phones
     * Listar teléfonos
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobPhone::query();

        // Filtros
        if ($request->has('id_client')) {
            $query->byClient($request->id_client);
        }

        if ($request->has('phone')) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('attrib1', 'like', "%{$search}%")
                  ->orWhere('attrib2', 'like', "%{$search}%")
                  ->orWhere('attrib3', 'like', "%{$search}%");
            });
        }

        // Relaciones
        if ($request->boolean('with_contacts')) {
            $query->with('contacts');
            $query->withCount('contacts');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'update_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $phones = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($phones);
    }

    /**
     * POST /api/job-phones
     * Crear un nuevo teléfono
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_client' => 'required|integer',
            'phone' => 'required|integer|digits:9',
            'attrib1' => 'nullable|string|max:50',
            'attrib2' => 'nullable|string|max:50',
            'attrib3' => 'nullable|string|max:50',
            'attrib4' => 'nullable|string|max:50',
            'attrib5' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['update_date'] = now()->toDateString();

        $phone = JobPhone::create($data);

        return $this->successResponse($phone, 'Phone created successfully', 201);
    }

    /**
     * GET /api/job-phones/{id}
     * Obtener un teléfono específico
     */
    public function show($id): JsonResponse
    {
        $phone = JobPhone::with('contacts')->withCount('contacts')->findOrFail($id);

        return $this->successResponse($phone);
    }

    /**
     * PUT /api/job-phones/{id}
     * Actualizar un teléfono
     */
    public function update(Request $request, $id): JsonResponse
    {
        $phone = JobPhone::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'phone' => 'sometimes|integer|digits:9',
            'attrib1' => 'nullable|string|max:50',
            'attrib2' => 'nullable|string|max:50',
            'attrib3' => 'nullable|string|max:50',
            'attrib4' => 'nullable|string|max:50',
            'attrib5' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['update_date'] = now()->toDateString();

        $phone->update($data);

        return $this->successResponse($phone, 'Phone updated successfully');
    }

    /**
     * DELETE /api/job-phones/{id}
     * Eliminar un teléfono
     */
    public function destroy($id): JsonResponse
    {
        $phone = JobPhone::findOrFail($id);
        
        // Verificar si tiene contactos asociados
        $contactsCount = $phone->contacts()->count();
        
        if ($contactsCount > 0) {
            return $this->errorResponse(
                "Cannot delete phone with {$contactsCount} associated contacts", 
                422
            );
        }

        $phone->delete();

        return $this->successResponse(null, 'Phone deleted successfully');
    }

    /**
     * GET /api/job-phones/by-client/{clientId}
     * Obtener todos los teléfonos de un cliente
     */
    public function byClient($clientId): JsonResponse
    {
        $phones = JobPhone::byClient($clientId)
                         ->with('contacts')
                         ->withCount('contacts')
                         ->orderBy('update_date', 'desc')
                         ->get();

        return $this->successResponse([
            'client_id' => $clientId,
            'total' => $phones->count(),
            'phones' => $phones,
        ]);
    }

    /**
     * POST /api/job-phones/bulk-create
     * Crear múltiples teléfonos
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phones' => 'required|array',
            'phones.*.id_client' => 'required|integer',
            'phones.*.phone' => 'required|integer|digits:9',
            'phones.*.attrib1' => 'nullable|string|max:50',
            'phones.*.attrib2' => 'nullable|string|max:50',
            'phones.*.attrib3' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $createdPhones = [];
        $updateDate = now()->toDateString();

        foreach ($request->phones as $phoneData) {
            $phoneData['update_date'] = $updateDate;
            $createdPhones[] = JobPhone::create($phoneData);
        }

        return $this->successResponse([
            'created_count' => count($createdPhones),
            'phones' => $createdPhones,
        ], 'Phones created successfully', 201);
    }
}