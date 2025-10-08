<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseController
{

    

     public function login(LoginRequest $request): JsonResponse
    {


          $user = User::on('secondary')
            ->where('email', $request->email)
            ->first();


        if (!$user || $user->password !== md5($request->password)) {
         
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'owner',
                'company_id' => 1,
            ],
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return $this->successResponse(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();


        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        return $this->successResponse([
            'id' => $user->id, // Usará el accessor getIdAttribute()
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'company_id' => $user->company_id,
            'last_login_at' => $user->last_login_at,
        ]);
    }
}