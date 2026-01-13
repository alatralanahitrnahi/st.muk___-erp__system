<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeController extends Controller
{
    public function getFeeStructures()
    {
        $structures = FeeStructure::with('program')->where('is_active', true)->get();
        return response()->json($structures);
    }

    public function createFeeStructure(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'academic_year' => 'required|string',
            'tuition_fee' => 'required|numeric|min:0',
            'development_fee' => 'numeric|min:0',
            'exam_fee' => 'numeric|min:0',
            'library_fee' => 'numeric|min:0',
            'other_fees' => 'numeric|min:0',
            'installments' => 'integer|min:1|max:12',
        ]);

        $totalFee = $request->tuition_fee + $request->development_fee + 
                   $request->exam_fee + $request->library_fee + $request->other_fees;

        $structure = FeeStructure::create(array_merge($request->all(), ['total_fee' => $totalFee]));
        
        return response()->json(['message' => 'Fee structure created', 'structure' => $structure], 201);
    }

    public function getStudentFees($studentId)
    {
        $fees = StudentFee::with(['feeStructure', 'payments'])
                          ->where('student_id', $studentId)
                          ->get();
        return response()->json($fees);
    }

    public function assignFeeToStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_structure_id' => 'required|exists:fee_structures,id',
            'discount_amount' => 'numeric|min:0',
        ]);

        $structure = FeeStructure::findOrFail($request->fee_structure_id);
        $discountAmount = $request->discount_amount ?? 0;
        $finalAmount = $structure->total_fee - $discountAmount;

        $studentFee = StudentFee::create([
            'student_id' => $request->student_id,
            'fee_structure_id' => $request->fee_structure_id,
            'total_amount' => $structure->total_fee,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'balance_amount' => $finalAmount,
        ]);

        return response()->json(['message' => 'Fee assigned to student', 'fee' => $studentFee], 201);
    }

    public function recordPayment(Request $request)
    {
        $request->validate([
            'student_fee_id' => 'required|exists:student_fees,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $studentFee = StudentFee::findOrFail($request->student_fee_id);
            
            if ($request->amount > $studentFee->balance_amount) {
                return response()->json(['error' => 'Payment amount exceeds balance'], 400);
            }

            $payment = FeePayment::create([
                'student_fee_id' => $request->student_fee_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'transaction_id' => $request->transaction_id,
                'remarks' => $request->remarks,
                'received_by' => $request->user()->id,
            ]);

            $newPaidAmount = $studentFee->paid_amount + $request->amount;
            $newBalanceAmount = $studentFee->final_amount - $newPaidAmount;
            $status = $newBalanceAmount == 0 ? 'paid' : ($newPaidAmount > 0 ? 'partial' : 'pending');

            $studentFee->update([
                'paid_amount' => $newPaidAmount,
                'balance_amount' => $newBalanceAmount,
                'status' => $status,
            ]);

            DB::commit();
            return response()->json(['message' => 'Payment recorded', 'payment' => $payment], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Payment failed'], 500);
        }
    }
}