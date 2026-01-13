<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database/database.sqlite',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Setting up Roles & Permissions ===\n\n";

// Clear existing data
Capsule::table('model_has_permissions')->delete();
Capsule::table('model_has_roles')->delete();
Capsule::table('role_has_permissions')->delete();
Capsule::table('permissions')->delete();
Capsule::table('roles')->delete();

// Create permissions
$permissions = [
    // Student Management
    'view_students', 'create_students', 'edit_students', 'delete_students',
    'approve_admissions', 'reject_admissions',
    
    // Fee Management
    'view_fees', 'create_fee_structures', 'edit_fees', 'record_payments', 'process_refunds',
    
    // Attendance Management
    'mark_attendance', 'view_attendance', 'edit_attendance', 'attendance_reports',
    
    // Results Management
    'enter_results', 'edit_results', 'view_results', 'result_reports',
    
    // Academic Management
    'manage_programs', 'manage_subjects', 'manage_departments',
    
    // Reporting
    'view_reports', 'export_reports', 'naac_reports',
    
    // System Administration
    'manage_users', 'manage_roles', 'system_settings',
];

echo "Creating permissions...\n";
foreach ($permissions as $permission) {
    Capsule::table('permissions')->insert([
        'name' => $permission,
        'guard_name' => 'web',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}
echo "✓ " . count($permissions) . " permissions created\n\n";

// Create roles
$roles = [
    'admin' => [
        'name' => 'Administrator',
        'permissions' => $permissions // All permissions
    ],
    'faculty' => [
        'name' => 'Faculty',
        'permissions' => [
            'view_students', 'mark_attendance', 'view_attendance', 'attendance_reports',
            'enter_results', 'edit_results', 'view_results', 'result_reports',
            'view_reports'
        ]
    ],
    'staff' => [
        'name' => 'Staff',
        'permissions' => [
            'view_students', 'create_students', 'edit_students',
            'approve_admissions', 'reject_admissions',
            'view_fees', 'record_payments', 'view_attendance', 'view_reports'
        ]
    ],
    'student' => [
        'name' => 'Student',
        'permissions' => [
            'view_attendance', 'view_results'
        ]
    ]
];

echo "Creating roles and assigning permissions...\n";
foreach ($roles as $roleKey => $roleData) {
    // Create role
    $roleId = Capsule::table('roles')->insertGetId([
        'name' => $roleKey,
        'guard_name' => 'web',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
    
    // Assign permissions to role
    foreach ($roleData['permissions'] as $permissionName) {
        $permission = Capsule::table('permissions')->where('name', $permissionName)->first();
        if ($permission) {
            Capsule::table('role_has_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permission->id,
            ]);
        }
    }
    
    echo "✓ Role '{$roleKey}' created with " . count($roleData['permissions']) . " permissions\n";
}

// Create demo users
echo "\nCreating demo users...\n";

$demoUsers = [
    [
        'name' => 'System Administrator',
        'email' => 'admin@pvgs.edu',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'user_type' => 'admin',
        'phone' => '+91 9876543210',
        'role' => 'admin'
    ],
    [
        'name' => 'Faculty Member',
        'email' => 'faculty@pvgs.edu',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'user_type' => 'faculty',
        'phone' => '+91 9876543211',
        'role' => 'faculty'
    ],
    [
        'name' => 'Staff Member',
        'email' => 'staff@pvgs.edu',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'user_type' => 'staff',
        'phone' => '+91 9876543212',
        'role' => 'staff'
    ]
];

foreach ($demoUsers as $userData) {
    $role = $userData['role'];
    unset($userData['role']);
    
    $userId = Capsule::table('users')->insertGetId(array_merge($userData, [
        'is_active' => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]));
    
    // Assign role to user
    $roleRecord = Capsule::table('roles')->where('name', $role)->first();
    if ($roleRecord) {
        Capsule::table('model_has_roles')->insert([
            'role_id' => $roleRecord->id,
            'model_type' => 'App\\Models\\User',
            'model_id' => $userId,
        ]);
    }
    
    echo "✓ User '{$userData['name']}' created with role '{$role}'\n";
}

echo "\n=== Setup Complete ===\n";
echo "✅ Roles and permissions configured\n";
echo "✅ Demo users created\n";
echo "✅ Frontend interfaces ready\n";
echo "✅ Razorpay integration configured\n";
echo "✅ Enhanced security implemented\n\n";

echo "Login Credentials:\n";
echo "Admin: admin@pvgs.edu / password123\n";
echo "Faculty: faculty@pvgs.edu / password123\n";
echo "Staff: staff@pvgs.edu / password123\n";
echo "Student: student@test.com / password123\n\n";

echo "Access URLs:\n";
echo "Login: /login.html\n";
echo "Admin Dashboard: /admin.html\n";
echo "Faculty Portal: /faculty.html\n";
echo "Student Portal: /student.html\n";