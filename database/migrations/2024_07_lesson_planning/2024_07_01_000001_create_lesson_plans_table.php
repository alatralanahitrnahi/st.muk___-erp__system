<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->foreignId('faculty_id')->constrained('users');
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('program_id')->constrained();
            $table->foreignId('semester_id')->constrained();
            $table->foreignId('academic_year_id')->constrained();
            
            // Lesson Details
            $table->date('lesson_date');
            $table->integer('lesson_number');
            $table->string('topic');
            $table->json('objectives'); // SMART objectives array
            $table->json('materials_required'); // List of materials/resources
            
            // Teaching Methodology
            $table->enum('teaching_method', [
                '5E_model', 'gradual_release', 'gagne_nine_events', 
                'lecture', 'demonstration', 'group_work', 'practical', 'other'
            ]);
            $table->text('lesson_procedure'); // Step-by-step guide
            $table->string('assessment_method'); // Quiz, exit ticket, etc.
            $table->text('differentiation_notes')->nullable(); // Special needs adaptation
            
            // Post-lesson Reflection
            $table->text('reflection_notes')->nullable(); // What worked/didn't work
            $table->integer('planned_duration')->default(60); // minutes
            $table->integer('actual_duration')->nullable(); // minutes
            $table->integer('attendance_count')->nullable();
            
            // Approval Workflow
            $table->enum('status', ['draft', 'submitted', 'hod_approved', 'approved', 'rejected'])->default('draft');
            $table->timestamp('hod_approved_at')->nullable();
            $table->foreignId('hod_approved_by')->nullable()->constrained('users');
            $table->timestamp('principal_approved_at')->nullable();
            $table->foreignId('principal_approved_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['faculty_id', 'lesson_date']);
            $table->index(['subject_id', 'lesson_date']);
            $table->index('status');
            $table->unique(['faculty_id', 'subject_id', 'lesson_date', 'lesson_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};