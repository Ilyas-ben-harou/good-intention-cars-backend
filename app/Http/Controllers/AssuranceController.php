<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use Illuminate\Http\Request;

class AssuranceController extends Controller
{
    public function index(){
        $assurances=Assurance::all();
        return response()->json([
            'assurances'=>$assurances
        ]);
    }
    public function show($id){
        $assurance=Assurance::findOrFail($id);
        return response()->json([
            'assurance'=>$assurance
        ]);
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'company_name' => 'required|string|max:255',
            'policy_number' => 'required|string|max:255|unique:assurances,policy_number',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired',
        ]);

        // Create new assurance record
        $assurance = Assurance::create([
            'car_id' => $request->car_id,
            'company_name' => $request->company_name,
            'policy_number' => $request->policy_number,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'cost' => $request->cost,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Assurance created successfully!',
            'assurance' => $assurance
        ], 201);
    }
    public function update(Request $request, $id)
    {
        // Validate incoming request
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'policy_number' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'cost' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired',
        ]);

        try {
            // Find the assurance by ID
            $assurance = Assurance::findOrFail($id);

            // Update the assurance details
            $assurance->update([
                'company_name' => $validated['company_name'],
                'policy_number' => $validated['policy_number'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'cost' => $validated['cost'],
                'status' => $validated['status'],
            ]);

            // Return a success response
            return response()->json([
                'message' => 'Assurance updated successfully!',
                'assurance' => $assurance
            ], 200);
        } catch (\Exception $e) {
            // Return an error response in case of failure
            return response()->json([
                'error' => 'Error updating the assurance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Find the assurance by ID
            $assurance = Assurance::findOrFail($id);

            // Delete the assurance
            $assurance->delete();

            // Return success response
            return response()->json([
                'message' => 'Assurance deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            // Return error response if deletion fails
            return response()->json([
                'error' => 'Error deleting the assurance: ' . $e->getMessage()
            ], 500);
        }
    }
}
