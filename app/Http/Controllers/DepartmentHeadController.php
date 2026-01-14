<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentHeadController extends Controller
{
    public function assign(Request $request, int $departmentId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = DB::table('users')->find($request->user_id);

        if (!in_array($user->role, ['faculty', 'registrar', 'principal'])) {
            return response()->json(['error' => 'Only faculty, registrar, or principal can be assigned as department head'], 422);
        }

        DB::transaction(function () use ($departmentId, $request) {
            // Update department head
            DB::table('departments')
                ->where('id', $departmentId)
                ->update(['department_head_id' => $request->user_id]);

            // Ensure user has department_head role in user_departments
            DB::table('user_departments')->updateOrInsert(
                ['user_id' => $request->user_id, 'department_id' => $departmentId],
                ['role_in_department' => 'department_head', 'updated_at' => now()]
            );

            // Audit log
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'department_id' => $departmentId,
                'action' => 'department_head.assigned',
                'entity_type' => 'Department',
                'entity_id' => $departmentId,
                'new_values' => json_encode(['department_head_id' => $request->user_id]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        });

        return response()->json(['message' => 'Department head assigned successfully']);
    }

    public function remove(int $departmentId)
    {
        $department = DB::table('departments')->find($departmentId);

        if (!$department->department_head_id) {
            return response()->json(['error' => 'No department head assigned'], 404);
        }

        DB::transaction(function () use ($departmentId, $department) {
            $oldHeadId = $department->department_head_id;

            DB::table('departments')
                ->where('id', $departmentId)
                ->update(['department_head_id' => null]);

            // Update user_departments role back to original
            $user = DB::table('users')->find($oldHeadId);
            DB::table('user_departments')
                ->where('user_id', $oldHeadId)
                ->where('department_id', $departmentId)
                ->update(['role_in_department' => $user->role]);

            // Audit log
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'department_id' => $departmentId,
                'action' => 'department_head.removed',
                'entity_type' => 'Department',
                'entity_id' => $departmentId,
                'old_values' => json_encode(['department_head_id' => $oldHeadId]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        });

        return response()->json(['message' => 'Department head removed successfully']);
    }

    public function list()
    {
        $heads = DB::table('departments')
            ->join('users', 'departments.department_head_id', '=', 'users.id')
            ->select('departments.id', 'departments.name', 'users.id as head_id', 'users.name as head_name', 'users.email')
            ->whereNotNull('departments.department_head_id')
            ->get();

        return response()->json($heads);
    }
}
