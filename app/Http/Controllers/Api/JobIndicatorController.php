<?php

namespace App\Http\Controllers\Api;

use App\Models\JobIndicatorExecutive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class JobIndicatorController extends BaseController
{
    /**
     * GET /api/job-indicators
     * Listar indicadores de ejecutivos
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobIndicatorExecutive::query();

        // Filtros
        if ($request->has('id_executive')) {
            $query->byExecutive($request->id_executive);
        }

        if ($request->has('period')) {
            $query->byPeriod($request->period);
        }

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('period_from')) {
            $query->where('period', '>=', $request->period_from);
        }

        if ($request->has('period_to')) {
            $query->where('period', '<=', $request->period_to);
        }

        // Relaciones
        if ($request->boolean('with_executive')) {
            $query->with('executive:id_user,name,email');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'period');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder)->orderBy('type', 'asc');

        $indicators = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($indicators);
    }

    /**
     * POST /api/job-indicators
     * Crear un nuevo indicador
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|integer',
            'period' => 'required|string|max:6',
            'id_executive' => 'required|integer',
            'title' => 'required|string|max:100',
            'amount' => 'required|numeric',
            'maskAmount' => 'required|string|max:25',
            'footer' => 'nullable|string|max:100',
            'title_color' => 'nullable|string|max:20',
            'y1' => 'nullable|array',
            'x1' => 'nullable|array',
            'y2' => 'nullable|array',
            'x2' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        // Verificar si ya existe un indicador con el mismo type, period y executive
        $exists = JobIndicatorExecutive::where('type', $request->type)
                                      ->where('period', $request->period)
                                      ->where('id_executive', $request->id_executive)
                                      ->exists();

        if ($exists) {
            return $this->errorResponse(
                'An indicator with this type, period and executive already exists', 
                422
            );
        }

        $data = $validator->validated();
        $data['footer'] = $data['footer'] ?? '';
        $data['title_color'] = $data['title_color'] ?? '#000000';

        $indicator = JobIndicatorExecutive::create($data);
        $indicator->load('executive:id_user,name,email');

        return $this->successResponse($indicator, 'Indicator created successfully', 201);
    }

    /**
     * GET /api/job-indicators/{id}
     * Obtener un indicador específico
     */
    public function show($id): JsonResponse
    {
        $indicator = JobIndicatorExecutive::with('executive:id_user,name,email')
                                         ->findOrFail($id);

        return $this->successResponse($indicator);
    }

    /**
     * PUT /api/job-indicators/{id}
     * Actualizar un indicador
     */
    public function update(Request $request, $id): JsonResponse
    {
        $indicator = JobIndicatorExecutive::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:100',
            'amount' => 'sometimes|numeric',
            'maskAmount' => 'sometimes|string|max:25',
            'footer' => 'nullable|string|max:100',
            'title_color' => 'nullable|string|max:20',
            'y1' => 'nullable|array',
            'x1' => 'nullable|array',
            'y2' => 'nullable|array',
            'x2' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $indicator->update($validator->validated());
        $indicator->load('executive:id_user,name,email');

        return $this->successResponse($indicator, 'Indicator updated successfully');
    }

    /**
     * DELETE /api/job-indicators/{id}
     * Eliminar un indicador
     */
    public function destroy($id): JsonResponse
    {
        $indicator = JobIndicatorExecutive::findOrFail($id);
        $indicator->delete();

        return $this->successResponse(null, 'Indicator deleted successfully');
    }

    /**
     * GET /api/job-indicators/executive/{executiveId}/period/{period}
     * Obtener indicadores de un ejecutivo para un período específico
     */
    public function byExecutiveAndPeriod($executiveId, $period): JsonResponse
    {
        $indicators = JobIndicatorExecutive::byExecutive($executiveId)
                                          ->byPeriod($period)
                                          ->orderBy('type', 'asc')
                                          ->get();

        return $this->successResponse([
            'executive_id' => $executiveId,
            'period' => $period,
            'total' => $indicators->count(),
            'indicators' => $indicators,
        ]);
    }

    /**
     * GET /api/job-indicators/executive/{executiveId}/latest
     * Obtener los últimos indicadores de un ejecutivo
     */
    public function latestByExecutive($executiveId): JsonResponse
    {
        $latestPeriod = JobIndicatorExecutive::byExecutive($executiveId)
                                            ->orderBy('period', 'desc')
                                            ->value('period');

        if (!$latestPeriod) {
            return $this->successResponse([
                'executive_id' => $executiveId,
                'period' => null,
                'total' => 0,
                'indicators' => [],
            ]);
        }

        $indicators = JobIndicatorExecutive::byExecutive($executiveId)
                                          ->byPeriod($latestPeriod)
                                          ->orderBy('type', 'asc')
                                          ->get();

        return $this->successResponse([
            'executive_id' => $executiveId,
            'period' => $latestPeriod,
            'total' => $indicators->count(),
            'indicators' => $indicators,
        ]);
    }

    /**
     * GET /api/job-indicators/summary/{executiveId}
     * Obtener resumen de indicadores por período para un ejecutivo
     */
    public function summary($executiveId): JsonResponse
    {
        $indicators = JobIndicatorExecutive::byExecutive($executiveId)
                                          ->orderBy('period', 'desc')
                                          ->orderBy('type', 'asc')
                                          ->get()
                                          ->groupBy('period');

        $summary = $indicators->map(function ($periodIndicators, $period) {
            return [
                'period' => $period,
                'total_indicators' => $periodIndicators->count(),
                'total_amount' => $periodIndicators->sum('amount'),
                'avg_amount' => $periodIndicators->avg('amount'),
                'indicators' => $periodIndicators,
            ];
        })->values();

        return $this->successResponse([
            'executive_id' => $executiveId,
            'total_periods' => $summary->count(),
            'summary' => $summary,
        ]);
    }

    /**
     * GET /api/job-indicators/compare
     * Comparar indicadores entre múltiples ejecutivos
     */
    public function compare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'executive_ids' => 'required|array',
            'executive_ids.*' => 'integer',
            'period' => 'required|string|max:6',
            'type' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $query = JobIndicatorExecutive::whereIn('id_executive', $request->executive_ids)
                                     ->where('period', $request->period);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $indicators = $query->with('executive:id_user,name,email')
                           ->orderBy('id_executive')
                           ->orderBy('type')
                           ->get()
                           ->groupBy('id_executive');

        return $this->successResponse([
            'period' => $request->period,
            'type' => $request->type ?? 'all',
            'executives_count' => count($request->executive_ids),
            'comparison' => $indicators,
        ]);
    }

    /**
     * POST /api/job-indicators/bulk-create
     * Crear múltiples indicadores
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'indicators' => 'required|array',
            'indicators.*.type' => 'required|integer',
            'indicators.*.period' => 'required|string|max:6',
            'indicators.*.id_executive' => 'required|integer',
            'indicators.*.title' => 'required|string|max:100',
            'indicators.*.amount' => 'required|numeric',
            'indicators.*.maskAmount' => 'required|string|max:25',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $created = [];
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->indicators as $index => $indicatorData) {
                // Verificar si ya existe
                $exists = JobIndicatorExecutive::where('type', $indicatorData['type'])
                                              ->where('period', $indicatorData['period'])
                                              ->where('id_executive', $indicatorData['id_executive'])
                                              ->exists();

                if ($exists) {
                    $errors[] = "Indicator at index {$index} already exists";
                    continue;
                }

                $indicatorData['footer'] = $indicatorData['footer'] ?? '';
                $indicatorData['title_color'] = $indicatorData['title_color'] ?? '#000000';

                $created[] = JobIndicatorExecutive::create($indicatorData);
            }

            DB::commit();

            return $this->successResponse([
                'created_count' => count($created),
                'error_count' => count($errors),
                'errors' => $errors,
                'indicators' => $created,
            ], 'Bulk creation completed', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Bulk creation failed: ' . $e->getMessage(), 500);
        }
    }
}