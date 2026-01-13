<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $residents = Resident::with('houses')->paginate(20);
        return \App\Http\Resources\ResidentResource::collection($residents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'nik' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $resident = Resident::create($validated);
        return new \App\Http\Resources\ResidentResource($resident);
    }

    /**
     * Display the specified resource.
     */
    public function show(Resident $resident)
    {
        $resident->load('houses');
        return new \App\Http\Resources\ResidentResource($resident);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'name' => 'string',
            'nik' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $resident->update($validated);
        return new \App\Http\Resources\ResidentResource($resident);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resident $resident)
    {
        $resident->delete();
        return response()->json(['message' => 'Resident deleted successfully']);
    }
}
