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
        Schema::table('users', function (Blueprint $table) {
            // Add user_type enum
            $table->enum('user_type', ['student', 'faculty', 'staff', 'admin'])->after('name');
            
            // Add phone field
            $table->string('phone', 15)->nullable()->after('email');
            
            // Add is_active boolean with default true
            $table->boolean('is_active')->default(true)->after('phone');
            
            // Add indexes
            $table->index('user_type');
            $table->index('is_active');
            
            // Ensure email uniqueness (should already exist from Laravel default, but confirm)
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['user_type']);
            $table->dropIndex(['is_active']);
            $table->dropUnique(['email']);
            
            // Drop columns
            $table->dropColumn(['user_type', 'phone', 'is_active']);
        });
    }
};