<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->string('role_name', 50);
            $table->string('module_name', 50);
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->timestamps();
            
            $table->unique(['department_id', 'role_name', 'module_name'], 'dept_role_module_unique');
            $table->index(['department_id', 'module_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_permissions');
    }
};
