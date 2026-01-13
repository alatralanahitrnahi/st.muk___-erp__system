<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::where('is_active', true)->get();
        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:departments',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($request->all());
        
        return response()->json([
            'message' => 'Department created successfully',
            'department' => $department
        ], 201);
    }

    public function show($id)
    {
        $department = Department::with('programs')->findOrFail($id);
        return response()->json($department);
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:departments,code,' . $id,
            'description' => 'nullable|string',
        ]);

        $department->update($request->all());
        
        return response()->json([
            'message' => 'Department updated successfully',
            'department' => $department
        ]);
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        $department->update(['is_active' => false]);
        
        return response()->json(['message' => 'Department deactivated successfully']);
    }
}