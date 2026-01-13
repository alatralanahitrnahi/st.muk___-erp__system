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

echo "=== Creating Comprehensive Academic Structure ===\n\n";

// Drop existing tables to recreate with proper structure
$tables = [
    'faculty_assignments', 'subject_components', 'subject_offerings', 'subjects', 
    'academic_units', 'academic_patterns', 'programs', 'departments'
];

foreach ($tables as $table) {
    try {
        Capsule::schema()->dropIfExists($table);
    } catch (Exception $e) {
        // Ignore errors
    }
}

// Create departments table
Capsule::schema()->create('departments', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Create academic patterns table
Capsule::schema()->create('academic_patterns', function ($table) {
    $table->id();
    $table->string('name'); // Semester, Annual, etc.
    $table->string('code')->unique();
    $table->integer('total_units'); // 6 semesters, 3 years, etc.
    $table->string('unit_type'); // semester, year
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Create programs table (enhanced)
Capsule::schema()->create('programs', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->enum('level', ['UG', 'PG', 'Diploma', 'Certificate']);
    $table->unsignedBigInteger('department_id');
    $table->unsignedBigInteger('academic_pattern_id');
    $table->integer('duration_years');
    $table->integer('total_semesters');
    $table->text('description')->nullable();
    $table->json('eligibility_criteria')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->foreign('department_id')->references('id')->on('departments');
    $table->foreign('academic_pattern_id')->references('id')->on('academic_patterns');
});

// Create academic units table
Capsule::schema()->create('academic_units', function ($table) {
    $table->id();
    $table->unsignedBigInteger('program_id');
    $table->string('name'); // FY, SY, TY or Sem 1, Sem 2, etc.
    $table->string('code');
    $table->integer('sequence_number');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->foreign('program_id')->references('id')->on('programs');
    $table->unique(['program_id', 'code']);
});

// Create subject master table
Capsule::schema()->create('subject_masters', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('description')->nullable();
    $table->enum('type', ['theory', 'practical', 'project', 'seminar']);
    $table->integer('credits');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Create subject offerings table
Capsule::schema()->create('subject_offerings', function ($table) {
    $table->id();
    $table->unsignedBigInteger('subject_master_id');
    $table->unsignedBigInteger('program_id');
    $table->unsignedBigInteger('academic_unit_id');
    $table->string('academic_year');
    $table->boolean('is_mandatory')->default(true);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->foreign('subject_master_id')->references('id')->on('subject_masters');
    $table->foreign('program_id')->references('id')->on('programs');
    $table->foreign('academic_unit_id')->references('id')->on('academic_units');
});

// Create subject components table
Capsule::schema()->create('subject_components', function ($table) {
    $table->id();
    $table->unsignedBigInteger('subject_offering_id');
    $table->string('name'); // Theory, Practical, Project
    $table->string('code');
    $table->integer('max_marks');
    $table->integer('passing_marks');
    $table->decimal('weightage', 5, 2)->default(100.00);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->foreign('subject_offering_id')->references('id')->on('subject_offerings');
});

// Create faculty assignments table
Capsule::schema()->create('faculty_assignments', function ($table) {
    $table->id();
    $table->unsignedBigInteger('faculty_id');
    $table->unsignedBigInteger('subject_offering_id');
    $table->unsignedBigInteger('subject_component_id')->nullable();
    $table->string('academic_year');
    $table->enum('role', ['primary', 'secondary', 'assistant']);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->foreign('faculty_id')->references('id')->on('users');
    $table->foreign('subject_offering_id')->references('id')->on('subject_offerings');
    $table->foreign('subject_component_id')->references('id')->on('subject_components');
});

echo "✓ Academic structure tables created\n";

// Insert sample data
echo "\nInserting sample academic data...\n";

// Academic patterns
Capsule::table('academic_patterns')->insert([
    ['name' => 'Semester System', 'code' => 'SEM', 'total_units' => 6, 'unit_type' => 'semester', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
    ['name' => 'Annual System', 'code' => 'ANN', 'total_units' => 3, 'unit_type' => 'year', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
]);

// Update programs with academic pattern
Capsule::table('programs')->update(['academic_pattern_id' => 1]);

// Academic units for BSc CS
$programId = Capsule::table('programs')->where('code', 'BSC_CS')->value('id');
if ($programId) {
    $units = [
        ['program_id' => $programId, 'name' => 'First Year', 'code' => 'FY', 'sequence_number' => 1],
        ['program_id' => $programId, 'name' => 'Second Year', 'code' => 'SY', 'sequence_number' => 2],
        ['program_id' => $programId, 'name' => 'Third Year', 'code' => 'TY', 'sequence_number' => 3],
    ];
    
    foreach ($units as $unit) {
        Capsule::table('academic_units')->insert(array_merge($unit, [
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    }
}

// Subject masters
$subjects = [
    ['name' => 'Programming in C', 'code' => 'CS101', 'type' => 'theory', 'credits' => 4],
    ['name' => 'C Programming Lab', 'code' => 'CS101P', 'type' => 'practical', 'credits' => 2],
    ['name' => 'Mathematics I', 'code' => 'MATH101', 'type' => 'theory', 'credits' => 3],
    ['name' => 'Data Structures', 'code' => 'CS201', 'type' => 'theory', 'credits' => 4],
    ['name' => 'Data Structures Lab', 'code' => 'CS201P', 'type' => 'practical', 'credits' => 2],
    ['name' => 'Database Management', 'code' => 'CS301', 'type' => 'theory', 'credits' => 4],
];

foreach ($subjects as $subject) {
    Capsule::table('subject_masters')->insert(array_merge($subject, [
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]));
}

echo "✓ Sample academic data inserted\n";
echo "✅ Comprehensive academic structure created!\n";