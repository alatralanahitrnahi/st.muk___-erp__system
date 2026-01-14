<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('program_id')->constrained('departments');
            $table->index('department_id');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('subject_id')->constrained('departments');
            $table->index(['department_id', 'attendance_date']);
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('subject_id')->constrained('departments');
            $table->index(['department_id', 'academic_year']);
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('student_id')->constrained('departments');
            $table->index('department_id');
        });

        Schema::table('faculty_assignments', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained('departments');
            $table->index('department_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('program_id')->constrained('departments');
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id', 'attendance_date']);
            $table->dropColumn('department_id');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id', 'academic_year']);
            $table->dropColumn('department_id');
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('faculty_assignments', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
