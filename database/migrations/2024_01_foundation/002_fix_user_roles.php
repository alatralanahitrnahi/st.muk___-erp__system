<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update user_type enum to include all roles
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['super-admin', 'principal', 'registrar', 'faculty', 'student'])
                  ->after('name')
                  ->default('student');
            $table->index('user_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type']);
            $table->dropColumn('user_type');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['student', 'faculty', 'staff', 'admin'])
                  ->after('name');
            $table->index('user_type');
        });
    }
};