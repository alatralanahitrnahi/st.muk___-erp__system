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

echo "=== Creating Enhanced Student Management System ===\n\n";

// Drop and recreate student-related tables
$tables = ['student_profile_changes', 'student_documents', 'student_enrollments', 'students'];

foreach ($tables as $table) {
    try {
        Capsule::schema()->dropIfExists($table);
    } catch (Exception $e) {
        // Ignore errors
    }
}

// Enhanced students table
Capsule::schema()->create('students', function ($table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('student_id')->unique()->nullable();
    $table->string('prn_number')->unique()->nullable();
    $table->string('admission_number')->unique()->nullable();
    
    // Personal Details
    $table->string('first_name');
    $table->string('middle_name')->nullable();
    $table->string('last_name');
    $table->date('date_of_birth');
    $table->enum('gender', ['male', 'female', 'other']);
    $table->string('blood_group')->nullable();
    $table->string('nationality')->default('Indian');
    $table->string('religion')->nullable();
    
    // Contact Details
    $table->string('phone');
    $table->string('alternate_phone')->nullable();
    $table->text('permanent_address');
    $table->text('current_address')->nullable();
    $table->string('city');
    $table->string('state');
    $table->string('pincode');
    
    // Category & Reservation
    $table->unsignedBigInteger('category_id');
    $table->boolean('is_minority')->default(false);
    $table->boolean('is_pwd')->default(false);
    $table->string('caste_certificate_no')->nullable();
    
    // Parent/Guardian Details
    $table->string('father_name');
    $table->string('father_occupation')->nullable();
    $table->string('father_phone')->nullable();
    $table->string('mother_name');
    $table->string('mother_occupation')->nullable();
    $table->string('mother_phone')->nullable();
    $table->string('guardian_name')->nullable();
    $table->string('guardian_relation')->nullable();
    $table->string('guardian_phone')->nullable();
    
    // Academic Details
    $table->string('previous_school')->nullable();
    $table->string('previous_board')->nullable();
    $table->decimal('previous_percentage', 5, 2)->nullable();
    $table->integer('previous_year_of_passing')->nullable();
    
    // Application Details
    $table->enum('application_status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
    $table->date('application_date');
    $table->decimal('application_fee', 10, 2)->default(0);
    $table->boolean('application_fee_paid')->default(false);
    $table->string('application_fee_receipt')->nullable();
    
    // Admission Details
    $table->date('admission_date')->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->text('rejection_reason')->nullable();
    
    // Status
    $table->enum('status', ['active', 'inactive', 'graduated', 'dropped', 'transferred'])->default('active');
    $table->boolean('is_active')->default(true);
    
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users');
    $table->foreign('category_id')->references('id')->on('categories');
    $table->foreign('approved_by')->references('id')->on('users');
});

// Student enrollments table
Capsule::schema()->create('student_enrollments', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('program_id');
    $table->unsignedBigInteger('academic_unit_id');
    $table->string('academic_year');
    $table->date('enrollment_date');
    $table->enum('status', ['enrolled', 'promoted', 'detained', 'dropped'])->default('enrolled');
    $table->boolean('is_current')->default(true);
    $table->timestamps();
    
    $table->foreign('student_id')->references('id')->on('students');
    $table->foreign('program_id')->references('id')->on('programs');
    $table->foreign('academic_unit_id')->references('id')->on('academic_units');
});

// Student documents table
Capsule::schema()->create('student_documents', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->string('document_type'); // SSC, HSC, Caste Certificate, etc.
    $table->string('document_name');
    $table->string('file_path');
    $table->string('file_type');
    $table->integer('file_size');
    $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
    $table->text('verification_notes')->nullable();
    $table->unsignedBigInteger('verified_by')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->boolean('is_mandatory')->default(false);
    $table->timestamps();
    
    $table->foreign('student_id')->references('id')->on('students');
    $table->foreign('verified_by')->references('id')->on('users');
});

// Student profile changes table (for audit trail)
Capsule::schema()->create('student_profile_changes', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->string('field_name');
    $table->text('old_value')->nullable();
    $table->text('new_value');
    $table->text('reason');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->unsignedBigInteger('requested_by');
    $table->unsignedBigInteger('approved_by')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->text('approval_notes')->nullable();
    $table->timestamps();
    
    $table->foreign('student_id')->references('id')->on('students');
    $table->foreign('requested_by')->references('id')->on('users');
    $table->foreign('approved_by')->references('id')->on('users');
});

echo "✓ Enhanced student management tables created\n";

// Enhanced categories
Capsule::table('categories')->delete();
Capsule::table('categories')->insert([
    ['name' => 'Open', 'code' => 'OPEN', 'description' => 'Open Category', 'fee_percentage' => 100.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'OBC', 'code' => 'OBC', 'description' => 'Other Backward Class', 'fee_percentage' => 90.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'SC', 'code' => 'SC', 'description' => 'Scheduled Caste', 'fee_percentage' => 50.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'ST', 'code' => 'ST', 'description' => 'Scheduled Tribe', 'fee_percentage' => 50.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'EWS', 'code' => 'EWS', 'description' => 'Economically Weaker Section', 'fee_percentage' => 75.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'NT', 'code' => 'NT', 'description' => 'Nomadic Tribe', 'fee_percentage' => 60.00, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
]);

echo "✓ Enhanced categories created\n";
echo "✅ Enhanced student management system created!\n";