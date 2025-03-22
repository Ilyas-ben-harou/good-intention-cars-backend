<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReservationController extends Controller
{

    public function index()
    {
        $reservations = Reservation::all();
        return response()->json([
            'reservations' => $reservations
        ]);
    }


    public function create()
    {
        //
    }
    public function getClients()
    {
        $clients = Reservation::all('id', 'client_nom_complete', 'client_phone', 'status_client');
        return response()->json([
            'clients' => $clients
        ]);
    }
    public function updateStatusClient(Request $request, $id)
    {
        $reservation = Reservation::find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Réservation introuvable.'], 404);
        }

        $reservation->status_client = $request->input('status');
        $reservation->save();

        return response()->json(['message' => 'Statut du client mis à jour avec succès.'], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'client_nom_complete' => 'required|string',
            'client_phone' => 'required|string',
            'car_id' => 'required|exists:cars,id',
            'pickup_location' => 'required|string',
            'dropoff_location' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'montantTotal' => 'required|numeric',
            'gps' => 'required',
            'baby_seat' => 'required',
            'status_client' => 'required|in:pending,approved,rejected',
            'status' => 'required|in:coming,in progress,completed',
            'payment_status' => 'required|in:made,not made',
        ]);
        Reservation::create($request->all());
        $car = Car::findOrFail($request->car_id);
        $car->Disponibilite = false;
        $car->save(); 
        return response()->json(['message' => 'Réservation créée avec succès!']);
    }

    public function showClient($id)
    {
        $client = Reservation::findOrFail($id);
        return response()->json(['client' => $client]);
    }
    public function show($id)
    {
        $reservation = Reservation::with(['car'])->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Réservation introuvable'], 404);
        }


        return response()->json([
            'reservation' => [
                'id' => $reservation->id,
                'client_nom_complete' => $reservation->client_nom_complete,
                'client_phone' => $reservation->client_phone,
                'car_id' => $reservation->car->id,
                'car_marque' => $reservation->car->marque,
                'car_modele' => $reservation->car->modele,
                'car_annee' => $reservation->car->annee,
                'car_photos' => $reservation->car->photos,
                'date_debut' => $reservation->date_debut,
                'date_fin' => $reservation->date_fin,
                'montantTotal' => $reservation->montantTotal,
                'status_client' => $reservation->status_client,
                'status' => $reservation->status,
                'payment_status' => $reservation->payment_status,
            ]
        ]);
    }



    public function edit($id)
    {
        $reservation = Reservation::findOrFail($id);
        return response()->json(['reservation' => $reservation]);
    }


    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Validation des données
        $request->validate([
            'client_nom_complete' => 'required|string|max:255',
            'client_phone' => 'required|string|max:255',
            'car_id' => 'required|exists:cars,id',
            'pickup_location' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'montantTotal' => 'required|numeric',
            'gps' => 'boolean',
            'baby_seat' => 'boolean',
            'status_client' => 'required|in:pending,approved,rejected',
            'status' => 'required|in:coming,in progress,completed',
            'payment_status' => 'required|in:made,not made',
        ]);

        // Mise à jour des champs
        $reservation->client_nom_complete = $request->client_nom_complete;
        $reservation->client_phone = $request->client_phone;
        $reservation->car_id = $request->car_id;
        $reservation->pickup_location = $request->pickup_location;
        $reservation->dropoff_location = $request->dropoff_location;
        $reservation->date_debut = $request->date_debut;
        $reservation->date_fin = $request->date_fin;
        $reservation->montantTotal = $request->montantTotal;
        $reservation->gps = $request->has('gps') ? $request->gps : false;
        $reservation->baby_seat = $request->has('baby_seat') ? $request->baby_seat : false;
        $reservation->status_client = $request->status_client;
        $reservation->status = $request->status;
        $reservation->payment_status = $request->payment_status;

        $reservation->save();

        $car = Car::findOrFail($request->car_id);
        $car->Disponibilite = false;
        $car->save();

        return response()->json([
            'message' => 'Réservation mise à jour avec succès',
        ], 200);
    }
    public function updateStatusPayment($id, Request $request)
    {
        $request->validate([
            'payment_status' => 'required|in:made,not made',
        ]);
        $reservation = Reservation::findOrFail($id);
        $reservation->payment_status = $request->payment_status;
        $reservation->save();
        return response()->json([
            'message' => 'Status de paiment mise à jour avec succès',
        ], 200);
    }

    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:coming,in progress,completed',
        ]);
        $reservation = Reservation::findOrFail($id);
        if ($request->status==='completed') {
            $reservation->status = $request->status;
            $reservation->date_fin=Carbon::today();
            $reservation->save();
            $car=Car::findOrFail($reservation->car_id);
            $car->Disponibilite=true;
            $car->save();
        }else{
            $reservation->status = $request->status;
            $reservation->save();
        }
        return response()->json([
            'message' => 'Status de reservation mise à jour avec succès',
        ], 200);
    }


    public function destroy($id)
    {
        //
    }
}
