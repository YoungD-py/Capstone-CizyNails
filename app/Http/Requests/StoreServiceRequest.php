<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:1000',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0|max:9999.99',
            'enable_removal' => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required',
            'name.unique' => 'A service with this name already exists',
            'name.max' => 'Service name cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 1000 characters',
            'duration_minutes.required' => 'Service duration is required',
            'duration_minutes.min' => 'Duration must be at least 15 minutes',
            'duration_minutes.max' => 'Duration cannot exceed 480 minutes',
            'price.required' => 'Service price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',
            'price.max' => 'Price cannot exceed 9999.99',
        ];
    }
}
