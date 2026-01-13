<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdmissionRequest extends FormRequest
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
            'program_id' => 'required|exists:programs,id',
            'category_id' => 'required|exists:categories,id',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:15',
            'parent_email' => 'nullable|email|max:255',
            'previous_school' => 'required|string|max:255',
            'previous_percentage' => 'required|numeric|min:0|max:100',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'application_fee_paid' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'program_id.required' => 'Program selection is required.',
            'program_id.exists' => 'Selected program does not exist.',
            'category_id.required' => 'Category selection is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'parent_name.required' => 'Parent/Guardian name is required.',
            'parent_phone.required' => 'Parent/Guardian phone number is required.',
            'parent_email.email' => 'Parent/Guardian email must be a valid email address.',
            'previous_school.required' => 'Previous school name is required.',
            'previous_percentage.required' => 'Previous academic percentage is required.',
            'previous_percentage.numeric' => 'Percentage must be a number.',
            'previous_percentage.min' => 'Percentage cannot be less than 0.',
            'previous_percentage.max' => 'Percentage cannot be more than 100.',
            'documents.array' => 'Documents must be an array of files.',
            'documents.*.file' => 'Each document must be a valid file.',
            'documents.*.mimes' => 'Documents must be PDF, JPG, JPEG, or PNG files.',
            'documents.*.max' => 'Each document must not exceed 2MB.',
        ];
    }
}