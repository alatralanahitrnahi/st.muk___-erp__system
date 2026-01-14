<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('role_in_department', 50);
            $table->boolean('is_primary')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'department_id', 'role_in_department'], 'user_dept_role_unique');
            $table->index(['user_id', 'is_primary']);
            $table->index('department_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('primary_department_id')->nullable()->after('user_type')->constrained('departments');
            $table->index('primary_department_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['primary_department_id']);
            $table->dropIndex(['primary_department_id']);
            $table->dropColumn('primary_department_id');
        });

        Schema::dropIfExists('user_departments');
    }
};
