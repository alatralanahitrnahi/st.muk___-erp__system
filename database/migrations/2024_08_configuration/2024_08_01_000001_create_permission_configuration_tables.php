<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_module_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles');
            $table->string('module_name');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['role_id', 'module_name']);
        });

        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name');
            $table->string('entity_type');
            $table->integer('approval_order');
            $table->foreignId('approver_role_id')->constrained('roles');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->index(['workflow_name', 'approval_order']);
        });

        Schema::create('data_visibility_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles');
            $table->string('entity_type');
            $table->enum('scope', ['all', 'department', 'program', 'own', 'assigned']);
            $table->json('filters')->nullable();
            $table->timestamps();
            $table->unique(['role_id', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_visibility_rules');
        Schema::dropIfExists('approval_workflows');
        Schema::dropIfExists('role_module_access');
    }
};