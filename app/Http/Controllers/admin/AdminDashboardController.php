<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Client;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function getCarsStats()
    {
        $totalCars = Car::count();
        $availableCars = Car::where('Disponibilite', true)->count();

        return response()->json([
            'total' => $totalCars,
            'available' => $availableCars
        ]);
    }

    public function getClientsStats()
    {
        $totalClients = Reservation::count();
        $pendingClients = Reservation::where('status_client', 'pending')->count();

        return response()->json([
            'total' => $totalClients,
            'pending' => $pendingClients
        ]);
    }

    public function getReservationsStats()
    {
        $totalReservations = Reservation::count();
        $activeReservations = Reservation::whereIn('status', ['in progress', 'coming'])->count();

        return response()->json([
            'total' => $totalReservations,
            'active' => $activeReservations
        ]);
    }

    public function getRevenueStats()
    {
        // Total revenue
        $totalRevenue = Reservation::where('payment_status', 'made')->sum('montantTotal');

        // Monthly revenue for the last 6 months
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        
        $monthlyRevenue = Reservation::where('payment_status', 'made')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('SUM(montantTotal) as amount'),
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('MONTHNAME(created_at) as month')
            )
            ->groupBy('month_num', 'month')
            ->orderBy('month_num')
            ->get();

        return response()->json([
            'total' => $totalRevenue,
            'monthly' => $monthlyRevenue
        ]);
    }
    
    // Optional: Add a method to get recent reservations for the dashboard
    public function getRecentReservations()
{
    $threesDaysAgo = now()->subDays(1);
    
    $recentReservations = Reservation::with(['car:id,marque,modele'])
        ->where('created_at', '>=', $threesDaysAgo)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
        
    return response()->json($recentReservations);
}
}
