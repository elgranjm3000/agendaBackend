<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;

Route::prefix('v1')->group(function () {
    
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // Authentication
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        
        // Companies
        Route::apiResource('companies', CompanyController::class);
        
        // Users
        Route::apiResource('users', UserController::class);
        
        // Clients
        Route::apiResource('clients', ClientController::class);
        
        // Services
        Route::apiResource('services', ServiceController::class);
        
        // Appointments
        Route::apiResource('appointments', AppointmentController::class);
        Route::patch('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::patch('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::get('/appointments/availability/check', [AppointmentController::class, 'availability']);
        
        // Payments
        Route::apiResource('payments', PaymentController::class);
        Route::patch('/payments/{payment}/refund', [PaymentController::class, 'refund']);
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/daily-occupancy', [ReportController::class, 'dailyOccupancy']);
            Route::get('/sales-by-date-range', [ReportController::class, 'salesByDateRange']);
            Route::get('/frequent-clients', [ReportController::class, 'frequentClients']);
            Route::get('/monthly-overview', [ReportController::class, 'monthlyOverview']);
        });
        
        // Data Export
        Route::post('/export/{type}/{format}', function ($type, $format) {
            $filters = request()->all();
            
            \App\Jobs\ExportDataJob::dispatch(
                $type,
                $format,
                $filters,
                auth()->user()->company_id,
                auth()->id()
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Export started. You will be notified when it\'s ready.',
            ]);
        })->middleware('can:export-data');
    });
});
