<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use App\Models\Car;
use App\Models\Technical_visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CarController extends Controller
{

    public function index()
    {
        $cars = Car::with('assurances')->get();
        return response()->json([
            'cars' => $cars
        ]);
    }
    public function allCars()
    {
        $cars = Car::all();
        return response()->json([
            'cars' => $cars
        ]);
    }
    public function getFourCars()
    {

        $fourCras = Car::limit(4)
            ->get();

        return response()->json(['cars' => $fourCras]);
    }

    public function getActiveAssurance($id)
    {
        try {
            $car = Car::findOrFail($id);
            $activeAssurance = $car->assurances()->where('status', 'actif')->first();
            return response()->json([
                'success' => true,
                'assurance' => $activeAssurance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching insurance details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'immatriculation' => [
                'required',
                'string',
                'regex:/^\d{1,5}[-\s]?[\p{Arabic}A-Z0-9]{1,2}[-\s]?\d{1,3}$/u',
                'unique:cars,immatriculation'
            ],
            'marque' => 'required|string',
            'modele' => 'required|string',
            'engine_capacity' => 'required|numeric|min:0.5|max:8.0',
            'dors' => 'required|integer|min:2|max:6',
            'fuel_type' => 'required|in:essence,diesel',
            'type' => 'required|in:automatic,manual',
            'passengers' => 'required|integer|min:1|max:50',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'prixByDay' => 'required|numeric|min:0',
            'Disponibilite' => 'required|boolean',
            'description' => 'required|string',
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store('cars', 'public');
            }
        }

        $car = Car::create([
            'immatriculation' => $request->immatriculation,
            'marque' => $request->marque,
            'modele' => $request->modele,
            'engine_capacity' => $request->engine_capacity,
            'dors' => $request->dors,
            'fuel_type' => $request->fuel_type,
            'type' => $request->type,
            'passengers' => $request->passengers,
            'photos' => json_encode($photos),
            'prixByDay' => $request->prixByDay,
            'Disponibilite' => $request->Disponibilite,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Car added successfully', 'car' => $car]);
    }



    public function show($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['success' => false, 'message' => 'Car not found'], 404);
        }

        // Get active assurance for this car
        $activeAssurance = Assurance::where('car_id', $id)
            ->where('status', 'active')
            ->first();

        // Get the latest technical visit for this car
        $latestVisit = Technical_visit::where('car_id', $id)
            ->latest('visit_date')
            ->first();

        return response()->json([
            'success' => true,
            'car' => $car,
            'assurance' => $activeAssurance,
            'visit' => $latestVisit
        ], 200);
    }


    public function edit(Car $car)
    {
        //
    }


    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'immatriculation' => [
                    'required',
                    'string',
                    'regex:/^\d{1,5}[-\s]?[\p{Arabic}A-Z0-9]{1,2}[-\s]?\d{1,3}$/u',
                ],
                'marque' => 'required|string|max:255',
                'modele' => 'required|string|max:255',
                'dors' => 'required|integer|min:2|max:4',
                'engine_capacity' => 'required|numeric|min:0.5|max:8.0',
                'fuel_type' => 'required|in:essence,diesel',
                'type' => 'required|in:automatic,manual',
                'passengers' => 'required|integer|min:1|max:50',
                'prixByDay' => 'required|numeric|min:0',
                'Disponibilite' => 'required|boolean',
                'description' => 'required|string',
                'photos.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'removed_photos' => 'sometimes|json',
            ]);

            $car = Car::findOrFail($id);

            $car->immatriculation = $validatedData['immatriculation'];
            $car->marque = $validatedData['marque'];
            $car->modele = $validatedData['modele'];
            $car->dors = $validatedData['dors'];
            $car->engine_capacity = $validatedData['engine_capacity'];
            $car->fuel_type = $validatedData['fuel_type'];
            $car->type = $validatedData['type'];
            $car->passengers = $validatedData['passengers'];
            $car->prixByDay = $validatedData['prixByDay'];
            $car->Disponibilite = $validatedData['Disponibilite'];
            $car->description = $validatedData['description'];

            $currentPhotos = json_decode($car->photos, true) ?? [];

            if ($request->has('removed_photos')) {
                $removedIndexes = json_decode($request->removed_photos, true);
                $newPhotos = [];

                foreach ($currentPhotos as $index => $photo) {
                    if (!in_array($index, $removedIndexes)) {
                        $newPhotos[] = $photo;
                    } else {
                        if (Storage::disk('public')->exists($photo)) {
                            Storage::disk('public')->delete($photo);
                        }
                    }
                }

                $currentPhotos = $newPhotos;
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('cars', 'public');
                    $currentPhotos[] = $path;
                }
            }

            $car->photos = json_encode($currentPhotos);
            $car->save();

            return response()->json([
                'success' => true,
                'message' => 'Car updated successfully',
                'car' => $car
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update car',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function destroy($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Voiture non trouvée'], 404);
        }

        $car->delete();

        return response()->json(['message' => 'Voiture supprimée avec succès']);
    }
}
