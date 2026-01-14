<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DepartmentScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }

        // Get department from route parameter or query string
        $departmentId = $request->route('department') ?? $request->query('department_id');
        
        // If no department specified, use primary department (backward compatibility)
        if (!$departmentId && $user->primary_department_id) {
            $departmentId = $user->primary_department_id;
        }
        
        // Inject department context into request
        if ($departmentId) {
            // Verify user has access to this department
            if (!$this->userHasAccessToDepartment($user, $departmentId)) {
                return response()->json([
                    'error' => 'You do not have access to this department'
                ], 403);
            }
            
            $request->merge(['scoped_department_id' => $departmentId]);
        }
        
        return $next($request);
    }

    private function userHasAccessToDepartment($user, $departmentId): bool
    {
        // Super admin has access to all departments
        if ($user->user_type === 'super-admin') {
            return true;
        }

        // Check if user has primary department access
        if ($user->primary_department_id == $departmentId) {
            return true;
        }

        // Check user_departments junction table
        return $user->departments()->where('department_id', $departmentId)->exists();
    }
}
