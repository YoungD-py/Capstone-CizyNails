<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Http\Requests\StoreScheduleRequest;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function store(StoreScheduleRequest $request)
    {
        $validated = $request->validated();

        // Check for duplicate schedule
        $existingSchedule = Schedule::where('service_id', $validated['service_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->exists();

        if ($existingSchedule) {
            return response()->json([
                'message' => 'A schedule already exists for this service on this day',
            ], 400);
        }

        $schedule = Schedule::create($validated);

        return response()->json([
            'message' => 'Schedule created successfully',
            'schedule' => $schedule,
        ], 201);
    }

    public function update(Schedule $schedule, Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'is_available' => 'sometimes|boolean',
        ]);

        // If both times are provided, validate end_time is after start_time
        if (isset($validated['start_time']) && isset($validated['end_time'])) {
            if (strtotime($validated['end_time']) <= strtotime($validated['start_time'])) {
                return response()->json([
                    'message' => 'End time must be after start time',
                ], 400);
            }
        }

        $schedule->update($validated);

        return response()->json([
            'message' => 'Schedule updated successfully',
            'schedule' => $schedule,
        ]);
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json([
            'message' => 'Schedule deleted successfully',
        ]);
    }
}
