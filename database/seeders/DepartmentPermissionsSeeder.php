<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentPermissionsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Seeding department permissions...');

        $departments = DB::table('departments')->pluck('id');

        foreach ($departments as $deptId) {
            $this->seedDepartmentPermissions($deptId);
        }

        $this->command->info('✅ Department permissions seeded successfully');
    }

    private function seedDepartmentPermissions(int $deptId)
    {
        $permissions = [
            // Super Admin - Full access
            ['department_id' => $deptId, 'role' => 'super-admin', 'module' => 'students', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'super-admin', 'module' => 'attendance', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'super-admin', 'module' => 'results', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'super-admin', 'module' => 'fees', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'super-admin', 'module' => 'lesson_plans', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'super-admin', 'module' => 'reports', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => true, 'can_approve' => true],

            // Principal - Full access with approval rights
            ['department_id' => $deptId, 'role' => 'principal', 'module' => 'students', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'principal', 'module' => 'attendance', 'can_view' => true, 'can_create' => false, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'principal', 'module' => 'results', 'can_view' => true, 'can_create' => false, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'principal', 'module' => 'fees', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'principal', 'module' => 'lesson_plans', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'principal', 'module' => 'reports', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],

            // Department Head (HOD) - Department-level approval
            ['department_id' => $deptId, 'role' => 'department_head', 'module' => 'students', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'department_head', 'module' => 'attendance', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'department_head', 'module' => 'results', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'department_head', 'module' => 'fees', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'department_head', 'module' => 'lesson_plans', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => true],
            ['department_id' => $deptId, 'role' => 'department_head', 'module' => 'reports', 'can_view' => true, 'can_create' => true, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],

            // Registrar - Administrative access
            ['department_id' => $deptId, 'role' => 'registrar', 'module' => 'students', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'registrar', 'module' => 'attendance', 'can_view' => true, 'can_create' => false, 'can_edit' => true, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'registrar', 'module' => 'results', 'can_view' => true, 'can_create' => false, 'can_edit' => true, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'registrar', 'module' => 'fees', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'registrar', 'module' => 'lesson_plans', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'registrar', 'module' => 'reports', 'can_view' => true, 'can_create' => true, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],

            // Faculty - Teaching-focused access
            ['department_id' => $deptId, 'role' => 'faculty', 'module' => 'students', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'faculty', 'module' => 'attendance', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'faculty', 'module' => 'results', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'faculty', 'module' => 'fees', 'can_view' => false, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'faculty', 'module' => 'lesson_plans', 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'faculty', 'module' => 'reports', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],

            // Student - Read-only access
            ['department_id' => $deptId, 'role' => 'student', 'module' => 'students', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'student', 'module' => 'attendance', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'student', 'module' => 'results', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'student', 'module' => 'fees', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'student', 'module' => 'lesson_plans', 'can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
            ['department_id' => $deptId, 'role' => 'student', 'module' => 'reports', 'can_view' => false, 'can_create' => false, 'can_edit' => false, 'can_delete' => false, 'can_approve' => false],
        ];

        foreach ($permissions as $permission) {
            DB::table('department_permissions')->insertOrIgnore(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }
}
