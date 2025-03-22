<?php

namespace App\Http\Controllers;


use App\Models\Car;
use App\Models\Technical_visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TechnicalVisitController extends Controller
{
    /**
     * Display a listing of the technical visits.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $visits = Technical_visit::with('car')->orderBy('expiration_date')->get();

        return response()->json([
            'success' => true,
            'visits' => $visits
        ]);
    }

    /**
     * Store a newly created technical visit in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'visit_date' => 'required|date',
            'expiration_date' => 'required|date|after:visit_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $visit = Technical_visit::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Technical visit created successfully',
            'visit' => $visit
        ], 201);
    }

    /**
     * Display the specified technical visit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $visit = Technical_visit::with('car')->find($id);

        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Technical visit not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'visit' => $visit
        ]);
    }

    /**
     * Update the specified technical visit in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $visit = Technical_visit::find($id);

        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Technical visit not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'car_id' => 'sometimes|required|exists:cars,id',
            'visit_date' => 'sometimes|required|date',
            'expiration_date' => 'sometimes|required|date|after:visit_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $visit->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Technical visit updated successfully',
            'visit' => $visit
        ]);
    }

    /**
     * Remove the specified technical visit from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $visit = Technical_visit::find($id);

        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'Technical visit not found'
            ], 404);
        }

        $visit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Technical visit deleted successfully'
        ]);
    }

    /**
     * Get technical visits for a specific car.
     *
     * @param  int  $carId
     * @return \Illuminate\Http\Response
     */
    public function getVisitsByCar($carId)
    {
        $car = Car::find($carId);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        $visits = Technical_visit::where('car_id', $carId)
            ->orderBy('expiration_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'car' => $car,
            'visits' => $visits
        ]);
    }

    /**
     * Get all expired technical visits.
     *
     * @return \Illuminate\Http\Response
     */
    public function getExpiredVisits()
    {
        $visits = Technical_visit::expired()->with('car')->get();

        return response()->json([
            'success' => true,
            'visits' => $visits
        ]);
    }

    /**
     * Get all visits expiring soon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getExpiringSoonVisits(Request $request)
    {
        $days = $request->input('days', 30);
        $visits = Technical_visit::expiringSoon($days)->with('car')->get();

        return response()->json([
            'success' => true,
            'visits' => $visits,
            'days_threshold' => $days
        ]);
    }

    /**
     * Get the latest technical visit for a car.
     *
     * @param  int  $carId
     * @return \Illuminate\Http\Response
     */
    public function getLatestVisitForCar($carId)
    {
        $car = Car::find($carId);

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        $visit = Technical_visit::where('car_id', $carId)
            ->orderBy('expiration_date', 'desc')
            ->first();

        if (!$visit) {
            return response()->json([
                'success' => false,
                'message' => 'No technical visits found for this car'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'visit' => $visit,
            'is_active' => $visit->isActive(),
            'days_until_expiration' => $visit->daysUntilExpiration()
        ]);
    }
}
