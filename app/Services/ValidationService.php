<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

class ValidationService
{
    public function validateStudentAdmission(array $data)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'program_id' => 'required|exists:programs,id',
            'category_id' => 'required|exists:categories,id',
            'admission_number' => 'required|unique:students',
            'prn_number' => 'required|unique:students',
        ];

        return Validator::make($data, $rules);
    }

    public function validateExamMarks(array $data)
    {
        $rules = [
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks' => 'required|numeric|min:0|max:100',
        ];

        return Validator::make($data, $rules);
    }

    public function validateFeePayment(array $data)
    {
        $rules = [
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ];

        return Validator::make($data, $rules);
    }
}