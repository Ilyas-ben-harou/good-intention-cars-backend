<?php

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\Api\AuthAdminController;
use App\Http\Controllers\Api\AuthClientController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\AssuranceController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\TechnicalVisitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin routes


// Client routes
// Route::prefix('client')->group(function () {
//     // Public routes
//     Route::post('/register', [AuthClientController::class, 'register']);
//     Route::post('/login', [AuthClientController::class, 'login']);

//     // Protected routes
//     Route::middleware('auth:client')->group(function () {
//         Route::post('/logout', [AuthClientController::class, 'logout']);
//         Route::get('/profile', [AuthClientController::class, 'profile']);
//         // Add other client protected routes here
//     });
// });

Route::prefix('admin')->group(function () {
    // Public routes
    Route::post('/register', [AuthAdminController::class, 'register']);
    Route::post('/login', [AuthAdminController::class, 'login']);
    Route::post('/forgot-password', [AuthAdminController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthAdminController::class, 'resetPassword']);

    
    // Protected routes
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AuthAdminController::class, 'logout']);
        Route::get('/profile', [AuthAdminController::class, 'profile']);
        // Add other admin protected routes here

        Route::get('/client', [ReservationController::class, 'getClients']);
        Route::get('/client/{id}', [ReservationController::class, 'showClient']);
        Route::put('client/{id}/status', [ReservationController::class, 'updateStatusClient']);

        Route::post('/car', [CarController::class, 'store']);
        Route::get('/car', [CarController::class, 'index']);
        Route::get('/car/{id}', [CarController::class, 'show']);
        Route::post('/car/{id}', [CarController::class, 'update']);
        Route::delete('/car/{id}', [CarController::class, 'destroy']);
        Route::get('/car/{id}/assurance', [CarController::class, 'getActiveAssurance']);

        Route::get('/update-car-status', function () {
            $today = Carbon::today();

            // Find reservations that have ended
            $completedReservations = DB::table('reservations')
                ->where('date_fin', '<=', $today)
                ->where('status', 'in progress')
                ->get();

            foreach ($completedReservations as $reservation) {
                DB::table('reservations')
                    ->where('id', $reservation->id)
                    ->update(['status' => 'completed']);

                DB::table('cars')
                    ->where('id', $reservation->car_id)
                    ->update(['Disponibilite' => true]);
            }

            return response()->json(['message' => 'Car and reservation statuses updated successfully!']);
        });

        Route::get('/update-insurance-status', function () {
            $today = Carbon::today();

            DB::table('assurances')
                ->where('end_date', '<=', $today)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            DB::table('assurances')
                ->where('end_date', '>', $today)
                ->where('status', 'active')
                ->update(['status' => 'active']); // Assure que celles avec un futur `end_date` restent actives

            return response()->json(['message' => 'Les statuts des assurances ont été mis à jour avec succès']);
        });

        Route::get('/assurance', [AssuranceController::class, 'index']);
        Route::post('/assurance', [AssuranceController::class, 'store']);
        Route::get('/assurance/{id}', [AssuranceController::class, 'show']);
        Route::put('/assurance/{id}', [AssuranceController::class, 'update']);
        Route::delete('/assurance/{id}', [AssuranceController::class, 'destroy']);

        Route::group(['prefix' => 'technical-visits'], function () {

            Route::get('/car/{carId}', [TechnicalVisitController::class, 'getVisitsByCar']);
            Route::get('/car/{carId}/latest', [TechnicalVisitController::class, 'getLatestVisitForCar']);
            Route::get('/expired', [TechnicalVisitController::class, 'getExpiredVisits']);
            Route::get('/expiring-soon', [TechnicalVisitController::class, 'getExpiringSoonVisits']);

            Route::get('/', [TechnicalVisitController::class, 'index']);
            Route::post('/', [TechnicalVisitController::class, 'store']);
            Route::get('/{id}', [TechnicalVisitController::class, 'show']); // ✅ Vérifie que {id} est un nombre
            Route::put('/{id}', [TechnicalVisitController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [TechnicalVisitController::class, 'destroy'])->where('id', '[0-9]+');
        });

        Route::post('/reservation', [ReservationController::class, 'store']);
        Route::get('/reservation', [ReservationController::class, 'index']);
        Route::get('/reservation/{id}', [ReservationController::class, 'show']);
        Route::post('/reservation/edit/{id}', [ReservationController::class, 'update']);
        Route::get('/reservation/edit/{id}', [ReservationController::class, 'edit']);
        Route::put('reservation/{id}/update-payment', [ReservationController::class, 'updateStatusPayment']);
        Route::put('reservation/{id}/update-status', [ReservationController::class, 'updateStatus']);

        Route::get('/stats/cars', [AdminDashboardController::class, 'getCarsStats']);
        Route::get('/stats/clients', [AdminDashboardController::class, 'getClientsStats']);
        Route::get('/stats/reservations', [AdminDashboardController::class, 'getReservationsStats']);
        Route::get('/stats/revenue', [AdminDashboardController::class, 'getRevenueStats']);
        Route::get('/stats/recentRes', [AdminDashboardController::class, 'getRecentReservations']);
    });
});


Route::get('/car', [CarController::class, 'getFourCars']);
Route::get('/cars', [CarController::class, 'index']);
Route::get('/car/{id}', [CarController::class, 'show']);

Route::post('/reservation', [ReservationController::class, 'store']);
