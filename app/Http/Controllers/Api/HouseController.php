<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\House;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $houses = House::all();
        return \App\Http\Resources\HouseResource::collection($houses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'block' => 'required|string',
            'number' => 'required|string',
            'status' => 'required|in:occupied,vacant_land',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $house = House::create($validated);
        return new \App\Http\Resources\HouseResource($house);
    }

    /**
     * Display the specified resource.
     */
    public function show(House $house)
    {
        return new \App\Http\Resources\HouseResource($house);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, House $house)
    {
        $validated = $request->validate([
            'block' => 'string',
            'number' => 'string',
            'status' => 'in:occupied,vacant_land',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $house->update($validated);
        return new \App\Http\Resources\HouseResource($house);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(House $house)
    {
        $house->delete();
        return response()->json(['message' => 'House deleted successfully']);
    }
}
