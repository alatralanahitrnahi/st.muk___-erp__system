<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'program', 'category'])->get();
        return response()->json($students);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'program_id' => 'required|exists:programs,id',
            'category_id' => 'required|exists:categories,id',
            'parent_name' => 'required|string',
            'parent_phone' => 'required|string',
        ]);

        $student = Student::create($request->all());
        
        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student->load(['user', 'program', 'category'])
        ], 201);
    }

    public function show($id)
    {
        $student = Student::with(['user', 'program', 'category'])->findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $student->update($request->all());
        
        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student->load(['user', 'program', 'category'])
        ]);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        
        return response()->json(['message' => 'Student deleted successfully']);
    }

    public function approve($id)
    {
        $student = Student::findOrFail($id);
        $student->update([
            'application_status' => 'approved',
            'approved_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'Student approved successfully',
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
            'message' => 'Student rejected',
            'student' => $student->load(['user', 'program', 'category'])
        ]);
    }
}
