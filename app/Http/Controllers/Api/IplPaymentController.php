<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IplPayment;
use Illuminate\Http\Request;

class IplPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = IplPayment::with('house')->latest()->paginate(20);
        return \App\Http\Resources\IplPaymentResource::collection($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_id' => 'required|exists:houses,id',
            'amount' => 'required|numeric',
            'period' => 'required|string', // Format YYYY-MM
            'paid_at' => 'nullable|date',
            'status' => 'required|in:paid,unpaid,pending',
            'payer_name' => 'nullable|string',
        ]);

        $iplPayment = IplPayment::create($validated);
        return new \App\Http\Resources\IplPaymentResource($iplPayment);
    }

    /**
     * Display the specified resource.
     */
    public function show(IplPayment $iplPayment)
    {
        $iplPayment->load('house');
        return new \App\Http\Resources\IplPaymentResource($iplPayment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IplPayment $iplPayment)
    {
        $validated = $request->validate([
            'house_id' => 'exists:houses,id',
            'amount' => 'numeric',
            'period' => 'string',
            'paid_at' => 'nullable|date',
            'status' => 'in:paid,unpaid,pending',
            'payer_name' => 'nullable|string',
        ]);

        $iplPayment->update($validated);
        return new \App\Http\Resources\IplPaymentResource($iplPayment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IplPayment $iplPayment)
    {
        $iplPayment->delete();
        return response()->json(['message' => 'Payment deleted successfully']);
    }
}
