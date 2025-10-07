<?php

namespace App\Http\Controllers\Api;

use App\Models\JobAttrib;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobAttribController extends BaseController
{
    /**
     * GET /api/job-attribs
     * Listar configuraciones de atributos
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobAttrib::query();

        // Filtros
        if ($request->has('id_type')) {
            $query->byType($request->id_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                for ($i = 1; $i <= 22; $i++) {
                    $q->orWhere("attrib{$i}", 'like', "%{$search}%");
                }
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id_type');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $attribs = $query->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($attribs);
    }

    /**
     * POST /api/job-attribs
     * Crear nueva configuración de atributos
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'id_type' => 'required|integer',
        ];

        // Agregar reglas para todos los atributos
        for ($i = 1; $i <= 22; $i++) {
            $rules["attrib{$i}"] = 'nullable|string|max:50';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $attrib = JobAttrib::create($validator->validated());

        return $this->successResponse($attrib, 'Attrib configuration created successfully', 201);
    }

    /**
     * GET /api/job-attribs/{id}
     * Obtener configuración específica
     */
    public function show($id): JsonResponse
    {
        $attrib = JobAttrib::findOrFail($id);

        return $this->successResponse([
            'id' => $attrib->id,
            'id_type' => $attrib->id_type,
            'all_attributes' => $attrib->getAllAttributesArray(),
            'raw_data' => $attrib,
        ]);
    }

    /**
     * PUT /api/job-attribs/{id}
     * Actualizar configuración
     */
    public function update(Request $request, $id): JsonResponse
    {
        $attrib = JobAttrib::findOrFail($id);

        $rules = [
            'id_type' => 'sometimes|integer',
        ];

        // Agregar reglas para todos los atributos
        for ($i = 1; $i <= 22; $i++) {
            $rules["attrib{$i}"] = 'nullable|string|max:50';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $attrib->update($validator->validated());

        return $this->successResponse($attrib, 'Attrib configuration updated successfully');
    }

    /**
     * DELETE /api/job-attribs/{id}
     * Eliminar configuración
     */
    public function destroy($id): JsonResponse
    {
        $attrib = JobAttrib::findOrFail($id);
        $attrib->delete();

        return $this->successResponse(null, 'Attrib configuration deleted successfully');
    }

    /**
     * GET /api/job-attribs/by-type/{type}
     * Obtener todas las configuraciones de un tipo
     */
    public function byType($type): JsonResponse
    {
        $attribs = JobAttrib::byType($type)->get();

        $formatted = $attribs->map(function($attrib) {
            return [
                'id' => $attrib->id,
                'id_type' => $attrib->id_type,
                'attributes' => $attrib->getAllAttributesArray(),
            ];
        });

        return $this->successResponse([
            'type' => $type,
            'total' => $attribs->count(),
            'configurations' => $formatted,
        ]);
    }

    /**
     * GET /api/job-attribs/types
     * Obtener lista de tipos únicos
     */
    public function types(): JsonResponse
    {
        $types = JobAttrib::select('id_type')
                         ->distinct()
                         ->orderBy('id_type')
                         ->pluck('id_type');

        return $this->successResponse([
            'total' => $types->count(),
            'types' => $types,
        ]);
    }

    /**
     * GET /api/job-attribs/schema/{type}
     * Obtener el esquema de atributos para un tipo específico
     * (útil para formularios dinámicos)
     */
    public function schema($type): JsonResponse
    {
        $attrib = JobAttrib::byType($type)->first();

        if (!$attrib) {
            return $this->errorResponse("No configuration found for type {$type}", 404);
        }

        $schema = [];
        for ($i = 1; $i <= 22; $i++) {
            $key = "attrib{$i}";
            if (!empty($attrib->$key)) {
                $schema[] = [
                    'field' => $key,
                    'label' => $attrib->$key,
                    'order' => $i,
                ];
            }
        }

        return $this->successResponse([
            'type' => $type,
            'fields_count' => count($schema),
            'schema' => $schema,
        ]);
    }

    /**
     * POST /api/job-attribs/validate-data
     * Validar datos contra el esquema de atributos
     */
    public function validateData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_type' => 'required|integer',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $attrib = JobAttrib::byType($request->id_type)->first();

        if (!$attrib) {
            return $this->errorResponse("No configuration found for type {$request->id_type}", 404);
        }

        $validFields = $attrib->getAllAttributesArray();
        $providedData = $request->data;
        
        $validation = [
            'valid_fields' => [],
            'invalid_fields' => [],
            'missing_required' => [],
        ];

        // Validar cada campo proporcionado
        foreach ($providedData as $key => $value) {
            if (isset($validFields[$key])) {
                $validation['valid_fields'][] = $key;
            } else {
                $validation['invalid_fields'][] = $key;
            }
        }

        $isValid = empty($validation['invalid_fields']);

        return $this->successResponse([
            'is_valid' => $isValid,
            'validation' => $validation,
            'schema' => $validFields,
        ]);
    }

    /**
     * POST /api/job-attribs/duplicate/{id}
     * Duplicar una configuración de atributos
     */
    public function duplicate($id): JsonResponse
    {
        $original = JobAttrib::findOrFail($id);
        
        $newAttrib = $original->replicate();
        $newAttrib->save();

        return $this->successResponse($newAttrib, 'Attrib configuration duplicated successfully', 201);
    }

    /**
     * GET /api/job-attribs/export/{type}
     * Exportar configuración de atributos en formato JSON
     */
    public function export($type): JsonResponse
    {
        $attribs = JobAttrib::byType($type)->get();

        if ($attribs->isEmpty()) {
            return $this->errorResponse("No configurations found for type {$type}", 404);
        }

        $export = [
            'type' => $type,
            'exported_at' => now()->toDateTimeString(),
            'count' => $attribs->count(),
            'configurations' => $attribs->map(function($attrib) {
                return $attrib->getAllAttributesArray();
            }),
        ];

        return $this->successResponse($export);
    }

    /**
     * POST /api/job-attribs/import
     * Importar configuraciones de atributos
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|integer',
            'configurations' => 'required|array',
            'configurations.*.attrib1' => 'nullable|string|max:50',
            'replace_existing' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $replaceExisting = $request->boolean('replace_existing', false);
        
        if ($replaceExisting) {
            JobAttrib::byType($request->type)->delete();
        }

        $imported = [];
        foreach ($request->configurations as $config) {
            $config['id_type'] = $request->type;
            $imported[] = JobAttrib::create($config);
        }

        return $this->successResponse([
            'imported_count' => count($imported),
            'type' => $request->type,
            'replaced_existing' => $replaceExisting,
            'configurations' => $imported,
        ], 'Configurations imported successfully', 201);
    }
}