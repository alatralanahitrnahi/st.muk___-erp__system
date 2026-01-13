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

echo "Creating fee management tables...\n";

// Fee structures table
Capsule::schema()->create('fee_structures', function ($table) {
    $table->id();
    $table->unsignedBigInteger('program_id');
    $table->string('academic_year');
    $table->decimal('tuition_fee', 10, 2);
    $table->decimal('development_fee', 10, 2)->default(0);
    $table->decimal('exam_fee', 10, 2)->default(0);
    $table->decimal('library_fee', 10, 2)->default(0);
    $table->decimal('other_fees', 10, 2)->default(0);
    $table->decimal('total_fee', 10, 2);
    $table->integer('installments')->default(2);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Student fees table
Capsule::schema()->create('student_fees', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('fee_structure_id');
    $table->decimal('total_amount', 10, 2);
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->decimal('final_amount', 10, 2);
    $table->decimal('paid_amount', 10, 2)->default(0);
    $table->decimal('balance_amount', 10, 2);
    $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
    $table->timestamps();
});

// Fee payments table
Capsule::schema()->create('fee_payments', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_fee_id');
    $table->decimal('amount', 10, 2);
    $table->date('payment_date');
    $table->string('payment_mode');
    $table->string('transaction_id')->nullable();
    $table->text('remarks')->nullable();
    $table->unsignedBigInteger('received_by');
    $table->timestamps();
});

echo "✓ Fee management tables created\n";

// Attendance tables
echo "Creating attendance tables...\n";

Capsule::schema()->create('attendance_records', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('subject_id');
    $table->date('attendance_date');
    $table->enum('status', ['present', 'absent', 'late'])->default('present');
    $table->time('check_in_time')->nullable();
    $table->unsignedBigInteger('marked_by');
    $table->text('remarks')->nullable();
    $table->timestamps();
    $table->unique(['student_id', 'subject_id', 'attendance_date']);
});

echo "✓ Attendance tables created\n";

// Examination tables
echo "Creating examination tables...\n";

Capsule::schema()->create('subjects', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->unsignedBigInteger('program_id');
    $table->integer('semester');
    $table->integer('credits');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Capsule::schema()->create('exam_results', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('subject_id');
    $table->string('exam_type'); // internal, external, practical
    $table->decimal('marks_obtained', 5, 2);
    $table->decimal('max_marks', 5, 2);
    $table->decimal('percentage', 5, 2);
    $table->string('grade')->nullable();
    $table->enum('result', ['pass', 'fail', 'absent'])->default('pass');
    $table->string('academic_year');
    $table->integer('semester');
    $table->timestamps();
});

echo "✓ Examination tables created\n";
echo "All Phase 3 tables created successfully!\n";