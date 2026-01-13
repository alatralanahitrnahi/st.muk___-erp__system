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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            
            // Academic year name (e.g., "2024-2025")
            $table->string('year_name', 20);
            
            // Date range
            $table->date('start_date');
            $table->date('end_date');
            
            // Status flags
            $table->boolean('is_active')->default(false);
            $table->boolean('is_current')->default(false);
            
            // Timestamps
            $table->timestamps();
            
            // Constraints
            $table->unique('year_name');
            
            // Check constraint for date validation (MySQL 8.0+)
            $table->check('start_date < end_date');
            
            // Indexes
            $table->index('is_active');
            $table->index('is_current');
        });
        
        // Note: The constraint that only one record can have is_current = true
        // will be enforced through application logic in the AcademicYear model
        // using a custom validation rule or database trigger
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};