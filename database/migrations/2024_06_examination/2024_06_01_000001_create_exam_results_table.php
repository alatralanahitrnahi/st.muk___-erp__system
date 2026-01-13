<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_component_id')->constrained()->onDelete('cascade');
            $table->decimal('marks_obtained', 8, 2);
            $table->decimal('max_marks', 8, 2);
            $table->decimal('pass_marks', 8, 2);
            $table->enum('result_status', ['PASS', 'FAIL']);
            $table->enum('exam_attempt', ['REGULAR', 'ATKT', 'REVAL']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
