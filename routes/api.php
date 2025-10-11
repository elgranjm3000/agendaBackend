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
use App\Http\Controllers\Api\JobOfferController;
use App\Http\Controllers\Api\JobExecutiveController;
use App\Http\Controllers\Api\JobContactController;
use App\Http\Controllers\Api\JobPhoneController;
use App\Http\Controllers\Api\JobStatusController;
use App\Http\Controllers\Api\JobIndicatorController;
use App\Http\Controllers\Api\JobAttribController;
use App\Http\Controllers\Api\DashboardController;

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


        Route::prefix('job-offers')->group(function () {
            Route::get('/', [JobOfferController::class, 'index']);
            Route::post('/', [JobOfferController::class, 'store']);
            Route::get('/{id}', [JobOfferController::class, 'show']);
            Route::put('/{id}', [JobOfferController::class, 'update']);
            Route::delete('/{id}', [JobOfferController::class, 'destroy']);
            Route::post('/{id}/activate', [JobOfferController::class, 'activate']);
            Route::post('/{id}/deactivate', [JobOfferController::class, 'deactivate']);
            Route::get('/{id}/statistics', [JobOfferController::class, 'statistics']);
        });

        // Job Executives
        Route::prefix('job-executives')->group(function () {
            Route::get('/', [JobExecutiveController::class, 'index']);
            Route::post('/', [JobExecutiveController::class, 'store']);
            Route::get('/{id}', [JobExecutiveController::class, 'show']);
            Route::put('/{id}', [JobExecutiveController::class, 'update']);
            Route::delete('/{id}', [JobExecutiveController::class, 'destroy']);
            Route::put('/{id}/status', [JobExecutiveController::class, 'updateStatus']);
            Route::put('/{id}/schedule', [JobExecutiveController::class, 'schedule']);
            Route::get('/by-office/{officeId}', [JobExecutiveController::class, 'byOffice']);
            Route::post('/bulk-update-status', [JobExecutiveController::class, 'bulkUpdateStatus']);
        });

        // Job Contacts
        Route::prefix('job-contacts')->group(function () {
            Route::get('/', [JobContactController::class, 'index']);
            Route::post('/', [JobContactController::class, 'store']);
            Route::get('/{id}', [JobContactController::class, 'show']);
            Route::put('/{id}', [JobContactController::class, 'update']);
            Route::delete('/{id}', [JobContactController::class, 'destroy']);
            Route::get('/by-executive/{executiveId}', [JobContactController::class, 'byExecutive']);
            Route::get('/today/{executiveId}', [JobContactController::class, 'todayByExecutive']);
            Route::get('/week/{executiveId}', [JobContactController::class, 'weekByExecutive']);
            Route::get('/pending/{executiveId}', [JobContactController::class, 'pendingByExecutive']);
            Route::put('/{id}/reschedule', [JobContactController::class, 'reschedule']);
            Route::get('/statistics', [JobContactController::class, 'statistics']);
        });

        // Job Phones
        Route::prefix('job-phones')->group(function () {
            Route::get('/', [JobPhoneController::class, 'index']);
            Route::post('/', [JobPhoneController::class, 'store']);
            Route::get('/{id}', [JobPhoneController::class, 'show']);
            Route::put('/{id}', [JobPhoneController::class, 'update']);
            Route::delete('/{id}', [JobPhoneController::class, 'destroy']);
            Route::get('/by-client/{clientId}', [JobPhoneController::class, 'byClient']);
            Route::post('/bulk-create', [JobPhoneController::class, 'bulkCreate']);
        });

        // Job Status
        Route::prefix('job-status')->group(function () {
            Route::get('/client', [JobStatusController::class, 'indexClientStatus']);
            Route::post('/client', [JobStatusController::class, 'storeClientStatus']);
            Route::get('/client/{id}', [JobStatusController::class, 'showClientStatus']);
            Route::put('/client/{id}', [JobStatusController::class, 'updateClientStatus']);
            Route::delete('/client/{id}', [JobStatusController::class, 'destroyClientStatus']);
            
            Route::get('/contact', [JobStatusController::class, 'indexContactStatus']);
            Route::post('/contact', [JobStatusController::class, 'storeContactStatus']);
            Route::get('/contact/{id}', [JobStatusController::class, 'showContactStatus']);
            Route::put('/contact/{id}', [JobStatusController::class, 'updateContactStatus']);
            Route::delete('/contact/{id}', [JobStatusController::class, 'destroyContactStatus']);
            
            Route::get('/all', [JobStatusController::class, 'all']);
        });

        // Job Indicators
        Route::prefix('job-indicators')->group(function () {
            Route::get('/', [JobIndicatorController::class, 'index']);
            Route::post('/', [JobIndicatorController::class, 'store']);
            Route::get('/{id}', [JobIndicatorController::class, 'show']);
            Route::put('/{id}', [JobIndicatorController::class, 'update']);
            Route::delete('/{id}', [JobIndicatorController::class, 'destroy']);
            Route::get('/executive/{executiveId}/period/{period}', [JobIndicatorController::class, 'byExecutiveAndPeriod']);
            Route::get('/executive/{executiveId}/latest', [JobIndicatorController::class, 'latestByExecutive']);
            Route::get('/summary/{executiveId}', [JobIndicatorController::class, 'summary']);
            Route::post('/compare', [JobIndicatorController::class, 'compare']);
            Route::post('/bulk-create', [JobIndicatorController::class, 'bulkCreate']);
        });

        // Job Attribs
        Route::prefix('job-attribs')->group(function () {
            Route::get('/', [JobAttribController::class, 'index']);
            Route::post('/', [JobAttribController::class, 'store']);
            Route::get('/{id}', [JobAttribController::class, 'show']);
            Route::put('/{id}', [JobAttribController::class, 'update']);
            Route::delete('/{id}', [JobAttribController::class, 'destroy']);
            Route::get('/by-type/{type}', [JobAttribController::class, 'byType']);
            Route::get('/types/list', [JobAttribController::class, 'types']);
            Route::get('/schema/{type}', [JobAttribController::class, 'schema']);
            Route::post('/validate-data', [JobAttribController::class, 'validateData']);
            Route::post('/duplicate/{id}', [JobAttribController::class, 'duplicate']);
            Route::get('/export/{type}', [JobAttribController::class, 'export']);
            Route::post('/import', [JobAttribController::class, 'import']);
        });


        // Dashboard principal - Obtiene todos los indicadores
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

        // KPIs individuales (tipos 10-13)
        Route::get('/dashboard/kpi/10', [DashboardController::class, 'kpi10']);
        Route::get('/dashboard/kpi/11', [DashboardController::class, 'kpi11']);
        Route::get('/dashboard/kpi/12', [DashboardController::class, 'kpi12']);
        Route::get('/dashboard/kpi/13', [DashboardController::class, 'kpi13']);
        
        // Gráficos
        Route::get('/dashboard/chart/dual', [DashboardController::class, 'chartDual']);
        Route::get('/dashboard/chart/single', [DashboardController::class, 'chartSingle']);
        
        // Utilidades
        Route::get('/dashboard/periods', [DashboardController::class, 'periods']);
        Route::get('/dashboard/compare', [DashboardController::class, 'compare']);
        
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
