<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string',
            'program_id' => 'required|exists:programs,id',
            'category_id' => 'required|exists:categories,id',
            'parent_name' => 'required|string',
            'parent_phone' => 'required|string',
            'previous_school' => 'nullable|string',
            'previous_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make('password123'), // Default password
                'user_type' => 'student',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'program_id' => $request->program_id,
                'category_id' => $request->category_id,
                'application_status' => 'pending',
                'application_date' => now(),
                'parent_name' => $request->parent_name,
                'parent_phone' => $request->parent_phone,
                'parent_email' => $request->parent_email,
                'previous_school' => $request->previous_school,
                'previous_percentage' => $request->previous_percentage,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Admission application submitted successfully',
                'student' => $student->load(['user', 'program', 'category'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to submit application'], 500);
        }
    }

    public function show($id)
    {
        $student = Student::with(['user', 'program', 'category'])->findOrFail($id);
        return response()->json($student);
    }

    public function approve(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        
        $student->update([
            'application_status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'admission_number' => 'ADM' . date('Y') . str_pad($id, 4, '0', STR_PAD_LEFT),
        ]);

        return response()->json([
            'message' => 'Application approved successfully',
            'student' => $student->load(['user', 'program', 'category'])
        ]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $student = Student::findOrFail($id);
        
        $student->update([
            'application_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'message' => 'Application rejected',
            'student' => $student->load(['user', 'program', 'category'])
        ]);
    }
}