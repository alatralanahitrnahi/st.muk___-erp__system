<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('program_code', 20);
            $table->string('program_name');
            $table->text('description')->nullable();
            $table->integer('duration_years');
            $table->integer('total_semesters');
            $table->decimal('total_fees', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('program_code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};