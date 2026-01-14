<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrincipalConfigSeeder extends Seeder
{
    public function run(): void
    {
        // Default Module Permissions
        $defaultPermissions = [
            'students' => [
                'registrar' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true, 'export' => true, 'approve' => true],
                'faculty' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'export' => false, 'approve' => false],
                'student' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'export' => false, 'approve' => false]
            ],
            'attendance' => [
                'registrar' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => true, 'export' => true, 'approve' => false],
                'faculty' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false, 'export' => true, 'approve' => false],
                'student' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'export' => false, 'approve' => false]
            ],
            'fees' => [
                'registrar' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false, 'export' => true, 'approve' => true],
                'faculty' => ['view' => false, 'create' => false, 'edit' => false, 'delete' => false, 'export' => false, 'approve' => false],
                'student' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'export' => false, 'approve' => false]
            ],
            'results' => [
                'registrar' => ['view' => true, 'create' => false, 'edit' => true, 'delete' => false, 'export' => true, 'approve' => true],
                'faculty' => ['view' => true, 'create' => true, 'edit' => true, 'delete' => false, 'export' => true, 'approve' => false],
                'student' => ['view' => true, 'create' => false, 'edit' => false, 'delete' => false, 'export' => false, 'approve' => false]
            ]
        ];

        foreach ($defaultPermissions as $module => $roles) {
            foreach ($roles as $role => $perms) {
                DB::table('module_permissions')->insert([
                    'module_name' => $module,
                    'role_name' => $role,
                    'can_view' => $perms['view'],
                    'can_create' => $perms['create'],
                    'can_edit' => $perms['edit'],
                    'can_delete' => $perms['delete'],
                    'can_export' => $perms['export'],
                    'can_approve' => $perms['approve'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Default Visibility Rules
        $visibilityRules = [
            ['module_name' => 'students', 'role_name' => 'faculty', 'rule_type' => 'scope', 'rule_config' => json_encode(['scope' => 'assigned_classes'])],
            ['module_name' => 'students', 'role_name' => 'student', 'rule_type' => 'scope', 'rule_config' => json_encode(['scope' => 'own_profile'])],
            ['module_name' => 'attendance', 'role_name' => 'faculty', 'rule_type' => 'scope', 'rule_config' => json_encode(['scope' => 'assigned_subjects'])],
            ['module_name' => 'fees', 'role_name' => 'student', 'rule_type' => 'scope', 'rule_config' => json_encode(['scope' => 'own_records'])]
        ];

        foreach ($visibilityRules as $rule) {
            DB::table('visibility_rules')->insert(array_merge($rule, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // Default Approval Chains
        $approvalChains = [
            ['workflow_name' => 'admission_approval', 'module_name' => 'students', 'step_order' => 1, 'approver_role' => 'registrar', 'is_required' => true],
            ['workflow_name' => 'admission_approval', 'module_name' => 'students', 'step_order' => 2, 'approver_role' => 'principal', 'is_required' => true],
            ['workflow_name' => 'fee_waiver', 'module_name' => 'fees', 'step_order' => 1, 'approver_role' => 'registrar', 'is_required' => true],
            ['workflow_name' => 'fee_waiver', 'module_name' => 'fees', 'step_order' => 2, 'approver_role' => 'principal', 'is_required' => true, 'conditions' => json_encode(['amount_threshold' => 5000])],
            ['workflow_name' => 'result_publish', 'module_name' => 'results', 'step_order' => 1, 'approver_role' => 'faculty', 'is_required' => true],
            ['workflow_name' => 'result_publish', 'module_name' => 'results', 'step_order' => 2, 'approver_role' => 'registrar', 'is_required' => true]
        ];

        foreach ($approvalChains as $chain) {
            DB::table('approval_chains')->insert(array_merge($chain, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
