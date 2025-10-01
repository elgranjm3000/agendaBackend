<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends BaseController
{
    public function __construct()
    {
        //$this->authorizeResource(Client::class, 'client');
    }

    public function index(Request $request): JsonResponse
    {
        $clients = Client::query()
            ->when($request->search, function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            })
            ->withCount('appointments')
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = Client::create($request->validated());

        return $this->successResponse($client, 'Client created successfully', 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load(['appointments' => function($q) {
            $q->with(['service:id,name', 'user:id,name'])
              ->orderBy('start_time', 'desc')
              ->limit(10);
        }]);

        return $this->successResponse($client);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return $this->successResponse($client, 'Client updated successfully');
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return $this->successResponse(null, 'Client deleted successfully');
    }
}
