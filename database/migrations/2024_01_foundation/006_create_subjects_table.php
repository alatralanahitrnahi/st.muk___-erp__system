<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code', 20);
            $table->string('subject_name');
            $table->text('description')->nullable();
            $table->integer('credits');
            $table->enum('type', ['theory', 'practical', 'both']);
            $table->integer('theory_marks')->default(0);
            $table->integer('practical_marks')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('subject_code');
            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};