<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrincipalConfigController extends Controller
{
    // Get permission matrix for a module
    public function getModulePermissions($module)
    {
        $permissions = DB::table('module_permissions')
            ->where('module_name', $module)
            ->get()
            ->groupBy('role_name');

        return response()->json(['permissions' => $permissions]);
    }

    // Update permission matrix
    public function updatePermissions(Request $request)
    {
        $validated = $request->validate([
            'module_name' => 'required|string',
            'role_name' => 'required|string',
            'permissions' => 'required|array',
            'field_permissions' => 'nullable|array'
        ]);

        DB::table('module_permissions')->updateOrInsert(
            ['module_name' => $validated['module_name'], 'role_name' => $validated['role_name']],
            array_merge($validated['permissions'], [
                'field_permissions' => json_encode($validated['field_permissions'] ?? []),
                'updated_at' => now()
            ])
        );

        $this->logConfigChange('permission', $validated['module_name'], $validated);

        return response()->json(['success' => true]);
    }

    // Get visibility rules
    public function getVisibilityRules($module)
    {
        $rules = DB::table('visibility_rules')
            ->where('module_name', $module)
            ->where('is_active', true)
            ->get();

        return response()->json(['rules' => $rules]);
    }

    // Update visibility rules
    public function updateVisibilityRules(Request $request)
    {
        $validated = $request->validate([
            'module_name' => 'required|string',
            'role_name' => 'required|string',
            'rules' => 'required|array'
        ]);

        foreach ($validated['rules'] as $rule) {
            DB::table('visibility_rules')->updateOrInsert(
                [
                    'module_name' => $validated['module_name'],
                    'role_name' => $validated['role_name'],
                    'rule_type' => $rule['type']
                ],
                [
                    'rule_config' => json_encode($rule['config']),
                    'is_active' => true,
                    'updated_at' => now()
                ]
            );
        }

        $this->logConfigChange('visibility', $validated['module_name'], $validated);

        return response()->json(['success' => true]);
    }

    // Get approval chains
    public function getApprovalChains($workflow)
    {
        $chain = DB::table('approval_chains')
            ->where('workflow_name', $workflow)
            ->orderBy('step_order')
            ->get();

        return response()->json(['chain' => $chain]);
    }

    // Update approval chain
    public function updateApprovalChain(Request $request)
    {
        $validated = $request->validate([
            'workflow_name' => 'required|string',
            'module_name' => 'required|string',
            'steps' => 'required|array'
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('approval_chains')->where('workflow_name', $validated['workflow_name'])->delete();

            foreach ($validated['steps'] as $index => $step) {
                DB::table('approval_chains')->insert([
                    'workflow_name' => $validated['workflow_name'],
                    'module_name' => $validated['module_name'],
                    'step_order' => $index + 1,
                    'approver_role' => $step['role'],
                    'conditions' => json_encode($step['conditions'] ?? []),
                    'is_required' => $step['required'] ?? true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        });

        $this->logConfigChange('workflow', $validated['module_name'], $validated);

        return response()->json(['success' => true]);
    }

    // Export configuration
    public function exportConfig(Request $request)
    {
        $modules = $request->input('modules', []);
        
        $config = [
            'exported_at' => now(),
            'modules' => []
        ];

        foreach ($modules as $module) {
            $config['modules'][$module] = [
                'permissions' => DB::table('module_permissions')->where('module_name', $module)->get(),
                'visibility_rules' => DB::table('visibility_rules')->where('module_name', $module)->get(),
                'approval_chains' => DB::table('approval_chains')->where('module_name', $module)->get()
            ];
        }

        return response()->json($config);
    }

    // Import configuration
    public function importConfig(Request $request)
    {
        $config = $request->input('config');

        DB::transaction(function () use ($config) {
            foreach ($config['modules'] as $module => $data) {
                foreach ($data['permissions'] as $perm) {
                    DB::table('module_permissions')->updateOrInsert(
                        ['module_name' => $module, 'role_name' => $perm->role_name],
                        (array) $perm
                    );
                }
            }
        });

        return response()->json(['success' => true]);
    }

    // Get configuration history
    public function getHistory($module)
    {
        $history = DB::table('config_history')
            ->where('module_name', $module)
            ->orderBy('changed_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json(['history' => $history]);
    }

    // Private helper to log changes
    private function logConfigChange($type, $module, $newValue)
    {
        DB::table('config_history')->insert([
            'changed_by' => auth()->id(),
            'config_type' => $type,
            'module_name' => $module,
            'new_value' => json_encode($newValue),
            'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
