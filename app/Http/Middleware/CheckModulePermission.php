<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckModulePermission
{
    public function handle(Request $request, Closure $next, $module, $action = 'view')
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Super admin bypasses all checks
        if ($user->user_type === 'super-admin') {
            return $next($request);
        }

        // Get user's role
        $role = $user->user_type;

        // Check module permission
        $permission = DB::table('module_permissions')
            ->where('module_name', $module)
            ->where('role_name', $role)
            ->first();

        if (!$permission) {
            return response()->json(['error' => 'Access denied to this module'], 403);
        }

        // Check specific action permission
        $canPerform = match($action) {
            'view' => $permission->can_view,
            'create' => $permission->can_create,
            'edit' => $permission->can_edit,
            'delete' => $permission->can_delete,
            'export' => $permission->can_export,
            'approve' => $permission->can_approve,
            default => false
        };

        if (!$canPerform) {
            return response()->json(['error' => "You don't have permission to {$action} in this module"], 403);
        }

        return $next($request);
    }
}
