<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with('department')->where('is_active', true)->get();
        return response()->json($programs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:programs',
            'level' => 'required|in:UG,PG,Diploma,Certificate',
            'department_id' => 'required|exists:departments,id',
            'duration_years' => 'required|integer|min:1|max:10',
            'total_semesters' => 'required|integer|min:1|max:20',
            'description' => 'nullable|string',
        ]);

        $program = Program::create($request->all());
        
        return response()->json([
            'message' => 'Program created successfully',
            'program' => $program->load('department')
        ], 201);
    }

    public function show($id)
    {
        $program = Program::with('department')->findOrFail($id);
        return response()->json($program);
    }

    public function update(Request $request, $id)
    {
        $program = Program::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:programs,code,' . $id,
            'level' => 'required|in:UG,PG,Diploma,Certificate',
            'department_id' => 'required|exists:departments,id',
            'duration_years' => 'required|integer|min:1|max:10',
            'total_semesters' => 'required|integer|min:1|max:20',
            'description' => 'nullable|string',
        ]);

        $program->update($request->all());
        
        return response()->json([
            'message' => 'Program updated successfully',
            'program' => $program->load('department')
        ]);
    }

    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $program->update(['is_active' => false]);
        
        return response()->json(['message' => 'Program deactivated successfully']);
    }
}