<?php

// Simple script to run migrations manually
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

// Create tables manually
$capsule->schema()->create('users', function ($table) {
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

$capsule->schema()->create('roles', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
});

$capsule->schema()->create('permissions', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('guard_name');
    $table->timestamps();
});

$capsule->schema()->create('role_has_permissions', function ($table) {
    $table->unsignedBigInteger('permission_id');
    $table->unsignedBigInteger('role_id');
    $table->primary(['permission_id', 'role_id']);
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
});

$capsule->schema()->create('model_has_roles', function ($table) {
    $table->unsignedBigInteger('role_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    $table->primary(['role_id', 'model_id', 'model_type']);
    $table->index(['model_id', 'model_type']);
    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
});

$capsule->schema()->create('model_has_permissions', function ($table) {
    $table->unsignedBigInteger('permission_id');
    $table->string('model_type');
    $table->unsignedBigInteger('model_id');
    $table->primary(['permission_id', 'model_id', 'model_type']);
    $table->index(['model_id', 'model_type']);
    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
});

$capsule->schema()->create('audit_logs', function ($table) {
    $table->id();
    $table->string('entity_type');
    $table->unsignedBigInteger('entity_id');
    $table->string('action');
    $table->unsignedBigInteger('performed_by')->nullable();
    $table->json('old_value')->nullable();
    $table->json('new_value')->nullable();
    $table->timestamps();
    $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
});

echo "Basic tables created successfully!\n";
