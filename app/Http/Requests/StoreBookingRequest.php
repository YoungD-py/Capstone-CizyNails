<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addMonths(3)->toDateString(),
            'booking_time' => 'required|date_format:H:i',
            'needs_removal' => 'nullable|in:0,1',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Please select a service',
            'service_id.exists' => 'The selected service does not exist',
            'booking_date.required' => 'Please select a booking date',
            'booking_date.after_or_equal' => 'Booking date must be today or later',
            'booking_date.before_or_equal' => 'Bookings can only be made up to 3 months in advance',
            'booking_time.required' => 'Please select a time slot',
            'booking_time.date_format' => 'Invalid time format',
            'notes.max' => 'Notes cannot exceed 500 characters',
        ];
    }
}
