<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->with('company:id,name')
            ->when($request->search, function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['company_id'] = auth()->user()->company_id;
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $user->load('company:id,name');

        return $this->successResponse($user, 'User created successfully', 201);
    }

    public function show(User $user): JsonResponse
    {
        $user->load('company:id,name');
        return $this->successResponse($user);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        $user->load('company:id,name');

        return $this->successResponse($user, 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
}
