<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionConfigController extends Controller
{
    public function getModuleAccess($roleId)
    {
        return DB::table('role_module_access')
            ->where('role_id', $roleId)
            ->get();
    }

    public function updateModuleAccess(Request $request, $roleId)
    {
        $validated = $request->validate([
            'module_name' => 'required|string',
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean'
        ]);

        DB::table('role_module_access')->updateOrInsert(
            ['role_id' => $roleId, 'module_name' => $validated['module_name']],
            array_merge($validated, ['updated_at' => now()])
        );

        return response()->json(['message' => 'Module access updated']);
    }

    public function getApprovalWorkflows()
    {
        return DB::table('approval_workflows')
            ->join('roles', 'approval_workflows.approver_role_id', '=', 'roles.id')
            ->select('approval_workflows.*', 'roles.name as role_name')
            ->orderBy('workflow_name')
            ->orderBy('approval_order')
            ->get()
            ->groupBy('workflow_name');
    }

    public function updateApprovalWorkflow(Request $request)
    {
        $validated = $request->validate([
            'workflow_name' => 'required|string',
            'entity_type' => 'required|string',
            'approval_order' => 'required|integer',
            'approver_role_id' => 'required|exists:roles,id',
            'is_required' => 'boolean'
        ]);

        DB::table('approval_workflows')->updateOrInsert(
            [
                'workflow_name' => $validated['workflow_name'],
                'approval_order' => $validated['approval_order']
            ],
            array_merge($validated, ['updated_at' => now()])
        );

        return response()->json(['message' => 'Workflow updated']);
    }

    public function getVisibilityRules($roleId)
    {
        return DB::table('data_visibility_rules')
            ->where('role_id', $roleId)
            ->get();
    }

    public function updateVisibilityRule(Request $request, $roleId)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string',
            'scope' => 'required|in:all,department,program,own,assigned',
            'filters' => 'nullable|array'
        ]);

        DB::table('data_visibility_rules')->updateOrInsert(
            ['role_id' => $roleId, 'entity_type' => $validated['entity_type']],
            array_merge($validated, ['updated_at' => now()])
        );

        return response()->json(['message' => 'Visibility rule updated']);
    }
}