<?php

namespace App\Http\Controllers\Api;

use App\Models\JobOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobOfferController extends BaseController
{
    /**
     * GET /api/job-offers
     * Listar todas las ofertas de trabajo
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobOffer::query();

        // Filtros
        if ($request->has('is_life')) {
            $query->where('is_life', $request->boolean('is_life'));
        }

        if ($request->has('is_delete')) {
            $query->where('is_delete', $request->boolean('is_delete'));
        }

        if ($request->has('current')) {
            $query->current();
        }

        if ($request->has('active')) {
            $query->active();
        }

        // Búsqueda por descripción
        if ($request->has('search')) {
            $query->where('descrip', 'like', "%{$request->search}%");
        }

        // Filtro por rango de fechas
        if ($request->has('date_from')) {
            $query->where('date_begin', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date_end', '<=', $request->date_to);
        }

        // Relaciones
        if ($request->boolean('with_counts')) {
            $query->withCount(['dayExecutives', 'dayContacts']);
        }

        if ($request->boolean('with_relations')) {
            $query->with(['dayExecutives', 'dayContacts']);
        }

        $offers = $query->orderBy('date_begin', 'desc')
                       ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($offers);
    }

    /**
     * POST /api/job-offers
     * Crear una nueva oferta
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'descrip' => 'required|string|max:200',
            'date_begin' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_begin',
            'id_user' => 'required|integer',
            'is_life' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();
        $data['is_delete'] = false;
        $data['is_life'] = $data['is_life'] ?? true;

        $offer = JobOffer::create($data);

        return $this->successResponse($offer, 'Job offer created successfully', 201);
    }

    /**
     * GET /api/job-offers/{id}
     * Obtener una oferta específica
     */
    public function show($id): JsonResponse
    {
        $offer = JobOffer::with(['dayExecutives', 'dayContacts'])
                        ->withCount(['dayExecutives', 'dayContacts'])
                        ->findOrFail($id);

        return $this->successResponse($offer);
    }

    /**
     * PUT /api/job-offers/{id}
     * Actualizar una oferta
     */
    public function update(Request $request, $id): JsonResponse
    {
        $offer = JobOffer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'descrip' => 'sometimes|string|max:200',
            'date_begin' => 'sometimes|date',
            'date_end' => 'sometimes|date|after_or_equal:date_begin',
            'is_life' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $offer->update($validator->validated());

        return $this->successResponse($offer, 'Job offer updated successfully');
    }

    /**
     * DELETE /api/job-offers/{id}
     * Soft delete de una oferta
     */
    public function destroy($id): JsonResponse
    {
        $offer = JobOffer::findOrFail($id);
        $offer->update(['is_delete' => true, 'is_life' => false]);

        return $this->successResponse(null, 'Job offer deleted successfully');
    }

    /**
     * POST /api/job-offers/{id}/activate
     * Activar una oferta
     */
    public function activate($id): JsonResponse
    {
        $offer = JobOffer::findOrFail($id);
        $offer->update(['is_life' => true, 'is_delete' => false]);

        return $this->successResponse($offer, 'Job offer activated successfully');
    }

    /**
     * POST /api/job-offers/{id}/deactivate
     * Desactivar una oferta
     */
    public function deactivate($id): JsonResponse
    {
        $offer = JobOffer::findOrFail($id);
        $offer->update(['is_life' => false]);

        return $this->successResponse($offer, 'Job offer deactivated successfully');
    }

    /**
     * GET /api/job-offers/{id}/statistics
     * Obtener estadísticas de una oferta
     */
    public function statistics($id): JsonResponse
    {
        $offer = JobOffer::findOrFail($id);

        $totalExecutives = $offer->dayExecutives()->count();
        $totalContacts = $offer->dayContacts()->count();
        $scheduledContacts = $offer->dayContacts()->whereNotNull('scheduled_date')->count();
        
        $executivesByStatus = $offer->dayExecutives()
            ->selectRaw('id_status, COUNT(*) as total')
            ->groupBy('id_status')
            ->get();

        $contactsByStatus = $offer->dayContacts()
            ->selectRaw('id_contact, COUNT(*) as total')
            ->groupBy('id_contact')
            ->get();

        return $this->successResponse([
            'offer' => $offer,
            'statistics' => [
                'total_executives' => $totalExecutives,
                'total_contacts' => $totalContacts,
                'scheduled_contacts' => $scheduledContacts,
                'executives_by_status' => $executivesByStatus,
                'contacts_by_status' => $contactsByStatus,
            ]
        ]);
    }
}