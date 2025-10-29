<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Http\Requests\StoreServiceRequest;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function store(StoreServiceRequest $request)
    {
        $service = Service::create($request->validated());

        return response()->json([
            'message' => 'Service created successfully',
            'service' => $service,
        ], 201);
    }

    public function update(Service $service, Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string|max:1000',
            'duration' => 'sometimes|integer|min:15|max:480',
            'price' => 'sometimes|numeric|min:0|max:9999.99',
        ]);

        $service->update($validated);

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service,
        ]);
    }

    public function destroy(Service $service)
    {
        // Check if service has bookings
        if ($service->bookings()->where('status', '!=', 'cancelled')->exists()) {
            return response()->json([
                'message' => 'Cannot delete service with active bookings',
            ], 400);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully',
        ]);
    }
}
   