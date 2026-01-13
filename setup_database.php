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

echo "Setting up database tables...\n";

// Drop existing tables
$tables = ['model_has_permissions', 'model_has_roles', 'role_has_permissions', 'permissions', 'roles', 'students', 'categories', 'programs', 'departments', 'users', 'audit_logs', 'personal_access_tokens'];
foreach ($tables as $table) {
    try {
        Capsule::schema()->dropIfExists($table);
    } catch (Exception $e) {
        // Ignore errors
    }
}

// Create users table
Capsule::schema()->create('users', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->string('password');
    $table->enum('user_type', ['student', 'faculty', 'staff', 'admin'])->default('student');
    $table->boolean('is_active')->default(true);
    $table->timestamp('email_verified_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
});

// Create roles table
Capsule::schema()->create('roles', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
});

// Create permissions table
Capsule::schema()->create('permissions', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
});

// Create role_has_permissions table
Capsule::schema()->create('role_has_permissions', function ($table) {
    $table->unsignedBigInteger('permission_id');
    $table->unsignedBigInteger('role_id');
    $table->primary(['permission_id', 'role_id']);
});

// Create model_has_roles table
Capsule::schema()->create('model_has_roles', function ($table) {
    $table->unsignedBigInteger('role_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    $table->primary(['role_id', 'model_id', 'model_type']);
    $table->index(['model_id', 'model_type']);
});

// Create model_has_permissions table
Capsule::schema()->create('model_has_permissions', function ($table) {
    $table->unsignedBigInteger('permission_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    $table->primary(['permission_id', 'model_id', 'model_type']);
    $table->index(['model_id', 'model_type']);
});

// Create departments table
Capsule::schema()->create('departments', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Create categories table
Capsule::schema()->create('categories', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->decimal('fee_percentage', 5, 2)->default(100.00);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Create programs table
Capsule::schema()->create('programs', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->enum('level', ['UG', 'PG', 'Diploma', 'Certificate']);
    $table->unsignedBigInteger('department_id');
    $table->integer('duration_years');
    $table->integer('total_semesters');
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Create students table
Capsule::schema()->create('students', function ($table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->unsignedBigInteger('program_id');
    $table->unsignedBigInteger('category_id');
    $table->string('admission_number')->unique()->nullable();
    $table->string('prn_number')->unique()->nullable();
    $table->enum('status', ['active', 'inactive', 'graduated', 'dropped'])->default('active');
    $table->boolean('scholarship_applied')->default(false);
    $table->date('admission_date')->nullable();
    $table->json('documents')->nullable();
    $table->enum('application_status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->date('application_date');
    $table->boolean('application_fee_paid')->default(false);
    $table->string('parent_name');
    $table->string('parent_phone');
    $table->string('parent_email')->nullable();
    $table->string('previous_school')->nullable();
    $table->decimal('previous_percentage', 5, 2)->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
});

// Create audit_logs table
Capsule::schema()->create('audit_logs', function ($table) {
    $table->id();
    $table->string('entity_type');
    $table->unsignedBigInteger('entity_id');
    $table->string('action');
    $table->unsignedBigInteger('performed_by')->nullable();
    $table->json('old_value')->nullable();
    $table->json('new_value')->nullable();
    $table->timestamps();
});

// Create personal_access_tokens table
Capsule::schema()->create('personal_access_tokens', function ($table) {
    $table->id();
    $table->morphs('tokenable');
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});

echo "✓ All tables created successfully!\n";

// Insert initial data
echo "Inserting initial data...\n";

// Insert roles
Capsule::table('roles')->insert([
    ['name' => 'admin', 'guard_name' => 'web', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'faculty', 'guard_name' => 'web', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'student', 'guard_name' => 'web', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
]);

// Insert categories
Capsule::table('categories')->insert([
    ['name' => 'General', 'code' => 'GEN', 'fee_percentage' => 100.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'OBC', 'code' => 'OBC', 'fee_percentage' => 90.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'SC/ST', 'code' => 'SCST', 'fee_percentage' => 50.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
]);

// Insert departments
Capsule::table('departments')->insert([
    ['name' => 'Computer Science', 'code' => 'CS', 'description' => 'Computer Science Department', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'Commerce', 'code' => 'COM', 'description' => 'Commerce Department', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
]);

// Insert programs
Capsule::table('programs')->insert([
    ['name' => 'BSc Computer Science', 'code' => 'BSC_CS', 'level' => 'UG', 'department_id' => 1, 'duration_years' => 3, 'total_semesters' => 6, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'BCom', 'code' => 'BCOM', 'level' => 'UG', 'department_id' => 2, 'duration_years' => 3, 'total_semesters' => 6, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
]);

echo "✓ Initial data inserted successfully!\n";
echo "Database setup complete!\n";