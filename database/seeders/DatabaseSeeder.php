<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create departments
        $departments = [
            ['name' => 'Computer Science'],
            ['name' => 'Animation'],
            ['name' => 'Commerce'],
        ];

        foreach ($departments as $dept) {
            \App\Models\Department::create($dept);
        }

        // Create categories
        $categories = [
            ['name' => 'Open', 'code' => 'OPEN'],
            ['name' => 'SC', 'code' => 'SC'],
            ['name' => 'ST', 'code' => 'ST'],
            ['name' => 'OBC', 'code' => 'OBC'],
            ['name' => 'EWS', 'code' => 'EWS'],
        ];

        foreach ($categories as $cat) {
            \App\Models\Category::create($cat);
        }

        // Create programs
        $programs = [
            [
                'name' => 'B.Sc. Computer Science',
                'level' => 'UG',
                'department_id' => 1,
                'duration_years' => 3,
                'is_active' => true
            ],
            [
                'name' => 'B.Sc. Animation',
                'level' => 'UG',
                'department_id' => 2,
                'duration_years' => 3,
                'is_active' => true
            ],
            [
                'name' => 'B.Com.',
                'level' => 'UG',
                'department_id' => 3,
                'duration_years' => 3,
                'is_active' => true
            ],
        ];

        foreach ($programs as $prog) {
            \App\Models\Program::create($prog);
        }

        // Create academic year
        \App\Models\AcademicYear::create([
            'name' => '2024-25',
            'is_current' => true
        ]);

        // Create roles - renamed admin to registrar to avoid confusion
        $roles = [
            'super-admin',
            'principal',
            'registrar',  // Changed from 'admin' to avoid developer confusion
            'faculty',
            'student',
        ];

        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::create(['name' => $role]);
        }

        // Create permissions
        $permissions = [
            'view students',
            'create students',
            'edit students',
            'delete students',
            'view programs',
            'create programs',
            'edit programs',
            'delete programs',
            'view subjects',
            'create subjects',
            'edit subjects',
            'delete subjects',
            'view attendance',
            'mark attendance',
            'view results',
            'enter results',
            'view fees',
            'manage fees',
            'view reports',
            'generate reports',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $rolePermissions = [
            'super-admin' => $permissions, // all permissions
            'principal' => [
                'view students', 'create students', 'edit students', 'delete students',
                'view programs', 'create programs', 'edit programs', 'delete programs',
                'view subjects', 'create subjects', 'edit subjects', 'delete subjects',
                'view attendance', 'mark attendance',
                'view results', 'enter results',
                'view fees', 'manage fees',
                'view reports', 'generate reports',
            ],
            'registrar' => [  // Renamed from 'admin'
                'view students', 'create students', 'edit students',
                'view programs', 'view subjects',
                'view attendance', 'mark attendance',
                'view results', 'enter results',
                'view fees', 'manage fees',
                'view reports', 'generate reports',
            ],
            'faculty' => [
                'view students',
                'view programs', 'view subjects',
                'view attendance', 'mark attendance',
                'view results', 'enter results',
            ],
            'student' => [
                'view programs', 'view subjects',
                'view attendance', 'view results', 'view fees',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = \Spatie\Permission\Models\Role::findByName($roleName);
            $role->syncPermissions($perms);
        }
    }
}
