<?php
namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(Service::class, 'service');
    }

    public function index(Request $request): JsonResponse
    {
        $services = Service::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->active !== null, fn($q) => $q->where('is_active', $request->boolean('active')))
            ->withCount('appointments')
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($services);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = Service::create($request->validated());

        return $this->successResponse($service, 'Service created successfully', 201);
    }

    public function show(Service $service): JsonResponse
    {
        $service->loadCount('appointments');
        return $this->successResponse($service);
    }

    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $service->update($request->validated());

        return $this->successResponse($service, 'Service updated successfully');
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return $this->successResponse(null, 'Service deleted successfully');
    }
}