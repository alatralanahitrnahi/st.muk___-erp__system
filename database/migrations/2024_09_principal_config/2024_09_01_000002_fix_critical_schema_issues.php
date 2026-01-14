<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // FIX #1: Students - Status Field Clarification
        if (Schema::hasColumn('students', 'status')) {
            Schema::table('students', function (Blueprint $table) {
                $table->renameColumn('status', 'enrollment_status');
            });
        }
        
        Schema::table('students', function (Blueprint $table) {
            $table->enum('application_status', ['pending', 'under_review', 'approved', 'rejected'])
                ->default('pending')
                ->after('id');
        });

        // FIX #2: Attendance - Subject Component Resolution
        Schema::table('attendance', function (Blueprint $table) {
            $table->enum('component_type', ['theory', 'lab', 'practical', 'tutorial'])
                ->default('theory')
                ->after('subject_id');
            $table->index(['subject_id', 'component_type', 'attendance_date']);
        });

        // FIX #3: Fees - Scholarship & Payment Logic
        Schema::table('student_fees', function (Blueprint $table) {
            $table->decimal('payable_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('balance_amount', 10, 2)->default(0)->after('paid_amount');
        });

        // FIX #4: Results - Marks Validation
        DB::statement('ALTER TABLE student_results ADD CONSTRAINT chk_marks_valid 
            CHECK (marks_obtained >= 0 AND marks_obtained <= max_marks)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE student_results DROP CONSTRAINT IF EXISTS chk_marks_valid');
        
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropColumn(['payable_amount', 'balance_amount']);
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn('component_type');
            $table->dropIndex(['subject_id', 'component_type', 'attendance_date']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('application_status');
            $table->renameColumn('enrollment_status', 'status');
        });
    }
};
