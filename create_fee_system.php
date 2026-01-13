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

echo "=== Creating Enhanced Fee Management System ===\n\n";

// Drop and recreate fee-related tables
$tables = ['fee_payments', 'fee_installments', 'student_fees', 'fee_structures', 'fee_types'];

foreach ($tables as $table) {
    try {
        Capsule::schema()->dropIfExists($table);
    } catch (Exception $e) {
        // Ignore errors
    }
}

// Fee types table
Capsule::schema()->create('fee_types', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->enum('type', ['tuition', 'development', 'exam', 'library', 'lab', 'admission', 'other']);
    $table->boolean('is_refundable')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Enhanced fee structures table
Capsule::schema()->create('fee_structures', function ($table) {
    $table->id();
    $table->unsignedBigInteger('program_id');
    $table->unsignedBigInteger('academic_unit_id');
    $table->string('academic_year');
    $table->string('name'); // "BSc CS FY 2024-25"
    
    // Fee components
    $table->decimal('tuition_fee', 10, 2)->default(0);
    $table->decimal('development_fee', 10, 2)->default(0);
    $table->decimal('exam_fee', 10, 2)->default(0);
    $table->decimal('library_fee', 10, 2)->default(0);
    $table->decimal('lab_fee', 10, 2)->default(0);
    $table->decimal('other_fees', 10, 2)->default(0);
    $table->decimal('total_fee', 10, 2);
    
    // Installment configuration
    $table->integer('total_installments')->default(1);
    $table->enum('installment_type', ['equal', 'custom'])->default('equal');
    
    // Category-wise fee variations
    $table->json('category_variations')->nullable(); // Store category-wise discounts
    
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->foreign('program_id')->references('id')->on('programs');
    $table->foreign('academic_unit_id')->references('id')->on('academic_units');
    $table->unique(['program_id', 'academic_unit_id', 'academic_year']);
});

// Fee installments table (defines installment structure)
Capsule::schema()->create('fee_installments', function ($table) {
    $table->id();
    $table->unsignedBigInteger('fee_structure_id');
    $table->integer('installment_number'); // 1, 2, 3...
    $table->string('installment_name'); // "First Installment", "Second Installment"
    $table->decimal('amount', 10, 2);
    $table->date('due_date');
    $table->integer('grace_period_days')->default(0);
    $table->decimal('late_fee_amount', 10, 2)->default(0);
    $table->boolean('is_mandatory')->default(true);
    $table->timestamps();
    
    $table->foreign('fee_structure_id')->references('id')->on('fee_structures');
    $table->unique(['fee_structure_id', 'installment_number']);
});

// Student fees table (individual student fee assignment)
Capsule::schema()->create('student_fees', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_id');
    $table->unsignedBigInteger('fee_structure_id');
    $table->string('academic_year');
    
    // Calculated amounts
    $table->decimal('gross_amount', 10, 2); // Before any adjustments
    $table->decimal('scholarship_amount', 10, 2)->default(0);
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->decimal('fine_amount', 10, 2)->default(0);
    $table->decimal('net_amount', 10, 2); // Final payable amount
    
    // Payment tracking
    $table->decimal('paid_amount', 10, 2)->default(0);
    $table->decimal('balance_amount', 10, 2);
    
    // Status tracking
    $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
    $table->integer('current_installment')->default(1);
    
    // Audit fields
    $table->unsignedBigInteger('assigned_by');
    $table->timestamp('assigned_at');
    $table->text('notes')->nullable();
    
    $table->timestamps();
    
    $table->foreign('student_id')->references('id')->on('students');
    $table->foreign('fee_structure_id')->references('id')->on('fee_structures');
    $table->foreign('assigned_by')->references('id')->on('users');
    $table->unique(['student_id', 'fee_structure_id', 'academic_year']);
});

// Fee payments table (individual payment records)
Capsule::schema()->create('fee_payments', function ($table) {
    $table->id();
    $table->unsignedBigInteger('student_fee_id');
    $table->integer('installment_number');
    $table->string('receipt_number')->unique();
    
    // Payment details
    $table->decimal('amount', 10, 2);
    $table->date('payment_date');
    $table->enum('payment_mode', ['cash', 'cheque', 'dd', 'online', 'card']);
    $table->string('transaction_reference')->nullable();
    $table->string('bank_name')->nullable();
    
    // Online payment details
    $table->string('gateway_transaction_id')->nullable();
    $table->string('gateway_response')->nullable();
    $table->enum('gateway_status', ['pending', 'success', 'failed', 'cancelled'])->nullable();
    
    // Breakdown
    $table->decimal('principal_amount', 10, 2);
    $table->decimal('late_fee_amount', 10, 2)->default(0);
    $table->decimal('adjustment_amount', 10, 2)->default(0);
    
    // Processing details
    $table->unsignedBigInteger('received_by');
    $table->timestamp('received_at');
    $table->text('remarks')->nullable();
    
    // Status
    $table->enum('status', ['pending', 'cleared', 'bounced', 'cancelled'])->default('cleared');
    
    $table->timestamps();
    
    $table->foreign('student_fee_id')->references('id')->on('student_fees');
    $table->foreign('received_by')->references('id')->on('users');
});

echo "✓ Enhanced fee management tables created\n";

// Insert fee types
$feeTypes = [
    ['name' => 'Tuition Fee', 'code' => 'TUITION', 'type' => 'tuition', 'is_refundable' => true],
    ['name' => 'Development Fee', 'code' => 'DEVELOPMENT', 'type' => 'development', 'is_refundable' => false],
    ['name' => 'Examination Fee', 'code' => 'EXAM', 'type' => 'exam', 'is_refundable' => false],
    ['name' => 'Library Fee', 'code' => 'LIBRARY', 'type' => 'library', 'is_refundable' => true],
    ['name' => 'Laboratory Fee', 'code' => 'LAB', 'type' => 'lab', 'is_refundable' => true],
    ['name' => 'Admission Fee', 'code' => 'ADMISSION', 'type' => 'admission', 'is_refundable' => false],
];

foreach ($feeTypes as $feeType) {
    Capsule::table('fee_types')->insert(array_merge($feeType, [
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]));
}

// Create sample fee structures
$programId = Capsule::table('programs')->where('code', 'BSC_CS')->value('id');
$academicUnitId = Capsule::table('academic_units')->where('code', 'FY')->value('id');

if ($programId && $academicUnitId) {
    // BSc CS FY Fee Structure
    $feeStructureId = Capsule::table('fee_structures')->insertGetId([
        'program_id' => $programId,
        'academic_unit_id' => $academicUnitId,
        'academic_year' => '2024-25',
        'name' => 'BSc Computer Science - First Year (2024-25)',
        'tuition_fee' => 45000.00,
        'development_fee' => 5000.00,
        'exam_fee' => 2000.00,
        'library_fee' => 1000.00,
        'lab_fee' => 3000.00,
        'other_fees' => 1000.00,
        'total_fee' => 57000.00,
        'total_installments' => 3,
        'installment_type' => 'custom',
        'category_variations' => json_encode([
            'OPEN' => ['discount_percentage' => 0],
            'OBC' => ['discount_percentage' => 10],
            'SC' => ['discount_percentage' => 50],
            'ST' => ['discount_percentage' => 50],
            'EWS' => ['discount_percentage' => 25],
            'NT' => ['discount_percentage' => 40],
        ]),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    // Create installments for the fee structure
    $installments = [
        [
            'installment_number' => 1,
            'installment_name' => 'Admission Installment',
            'amount' => 25000.00,
            'due_date' => '2024-06-30',
            'grace_period_days' => 15,
            'late_fee_amount' => 500.00
        ],
        [
            'installment_number' => 2,
            'installment_name' => 'Second Installment',
            'amount' => 20000.00,
            'due_date' => '2024-10-31',
            'grace_period_days' => 10,
            'late_fee_amount' => 300.00
        ],
        [
            'installment_number' => 3,
            'installment_name' => 'Final Installment',
            'amount' => 12000.00,
            'due_date' => '2025-01-31',
            'grace_period_days' => 10,
            'late_fee_amount' => 300.00
        ]
    ];

    foreach ($installments as $installment) {
        Capsule::table('fee_installments')->insert(array_merge($installment, [
            'fee_structure_id' => $feeStructureId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    }
}

echo "✓ Sample fee structures and installments created\n";
echo "✅ Enhanced fee management system with installment control created!\n";