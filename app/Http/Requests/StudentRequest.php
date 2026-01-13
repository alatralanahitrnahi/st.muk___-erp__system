<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'category_id' => 'required|exists:categories,id',
            'admission_number' => 'required|unique:students,admission_number',
            'prn_number' => 'required|unique:students,prn_number',
            'status' => 'in:active,inactive,graduated',
            'scholarship_applied' => 'boolean',
            'admission_date' => 'required|date',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,png|max:2048',
        ];
    }
}
