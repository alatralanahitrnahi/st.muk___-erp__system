<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidateDepartmentAccess
{
    public function handle(Request $request, Closure $next)
    {
        $departmentId = $request->route('departmentId') ?? $request->get('department_id');
        
        if (!$departmentId) {
            return $next($request);
        }

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => ['auth' => ['Authentication required']]
            ], 401);
        }

        // Super-admin and principal have access to all departments
        if (in_array($user->role, ['super-admin', 'principal'])) {
            $request->merge(['validated_department_id' => $departmentId]);
            return $next($request);
        }

        // Check user has access to department
        $hasAccess = DB::table('user_departments')
            ->where('user_id', $user->id)
            ->where('department_id', $departmentId)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this department',
                'errors' => ['department' => ['You do not have access to this department']]
            ], 403);
        }

        $request->merge(['validated_department_id' => $departmentId]);
        
        return $next($request);
    }
}
