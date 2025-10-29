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
            'duration' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0|max:9999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required',
            'name.unique' => 'A service with this name already exists',
            'name.max' => 'Service name cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 1000 characters',
            'duration.required' => 'Service duration is required',
            'duration.min' => 'Duration must be at least 15 minutes',
            'duration.max' => 'Duration cannot exceed 480 minutes',
            'price.required' => 'Service price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',
            'price.max' => 'Price cannot exceed 9999.99',
        ];
    }
}
