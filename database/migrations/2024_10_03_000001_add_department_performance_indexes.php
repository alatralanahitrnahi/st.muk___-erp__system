<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Students table indexes
        Schema::table('students', function (Blueprint $table) {
            $table->index(['department_id', 'created_at'], 'idx_students_dept_created');
            $table->index(['department_id', 'program_id'], 'idx_students_dept_program');
        });

        // Attendance table indexes
        Schema::table('attendance', function (Blueprint $table) {
            $table->index(['department_id', 'date'], 'idx_attendance_dept_date');
            $table->index(['department_id', 'student_id'], 'idx_attendance_dept_student');
        });

        // Results table indexes
        Schema::table('results', function (Blueprint $table) {
            $table->index(['department_id', 'academic_year', 'semester'], 'idx_results_dept_year_sem');
            $table->index(['department_id', 'student_id'], 'idx_results_dept_student');
        });

        // Fees table indexes
        Schema::table('fees', function (Blueprint $table) {
            $table->index(['department_id', 'status'], 'idx_fees_dept_status');
            $table->index(['department_id', 'student_id'], 'idx_fees_dept_student');
        });

        // Subjects table indexes
        Schema::table('subjects', function (Blueprint $table) {
            $table->index('department_id', 'idx_subjects_dept');
        });

        // Lesson plans table indexes
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->index(['department_id', 'subject_id'], 'idx_lesson_plans_dept_subject');
        });

        // User departments table indexes
        Schema::table('user_departments', function (Blueprint $table) {
            $table->index(['user_id', 'department_id'], 'idx_user_dept_composite');
            $table->index('department_id', 'idx_user_dept_dept');
        });

        // Workflow history table indexes
        Schema::table('workflow_history', function (Blueprint $table) {
            $table->index(['department_id', 'performed_at'], 'idx_workflow_dept_performed');
            $table->index(['entity_type', 'entity_id', 'department_id'], 'idx_workflow_entity_dept');
        });

        // Audit logs table indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['department_id', 'created_at'], 'idx_audit_dept_created');
            $table->index(['user_id', 'department_id'], 'idx_audit_user_dept');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('idx_students_dept_created');
            $table->dropIndex('idx_students_dept_program');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_dept_date');
            $table->dropIndex('idx_attendance_dept_student');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('idx_results_dept_year_sem');
            $table->dropIndex('idx_results_dept_student');
        });

        Schema::table('fees', function (Blueprint $table) {
            $table->dropIndex('idx_fees_dept_status');
            $table->dropIndex('idx_fees_dept_student');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex('idx_subjects_dept');
        });

        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropIndex('idx_lesson_plans_dept_subject');
        });

        Schema::table('user_departments', function (Blueprint $table) {
            $table->dropIndex('idx_user_dept_composite');
            $table->dropIndex('idx_user_dept_dept');
        });

        Schema::table('workflow_history', function (Blueprint $table) {
            $table->dropIndex('idx_workflow_dept_performed');
            $table->dropIndex('idx_workflow_entity_dept');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_dept_created');
            $table->dropIndex('idx_audit_user_dept');
        });
    }
};
