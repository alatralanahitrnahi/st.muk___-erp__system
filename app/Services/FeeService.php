<?php

namespace App\Services;

use App\Models\Student;
use App\Models\FeeStructure;

class FeeService
{
    public function calculateFees(Student $student)
    {
        // Logic to calculate fees based on program and category
        $feeStructure = FeeStructure::where('program_id', $student->program_id)
            ->where('category_id', $student->category_id)
            ->first();

        if ($feeStructure) {
            $totalFee = $feeStructure->total_fee;
            // Apply scholarship if any
            if ($student->scholarship_applied) {
                $totalFee -= $feeStructure->scholarship_amount ?? 0;
            }
            return $totalFee;
        }

        return 0;
    }

    public function generateInstallments(Student $student, $totalFee)
    {
        // Logic to generate fee installments
        $installments = [];
        // Example: 3 installments
        $installmentAmount = $totalFee / 3;
        for ($i = 1; $i <= 3; $i++) {
            $installments[] = [
                'student_id' => $student->id,
                'installment_number' => $i,
                'amount' => $installmentAmount,
                'due_date' => now()->addMonths($i),
            ];
        }
        return $installments;
    }
}
