<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module Permission Matrix
        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->string('role_name');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->json('field_permissions')->nullable();
            $table->timestamps();
            $table->unique(['module_name', 'role_name']);
        });

        // Data Visibility Rules
        Schema::create('visibility_rules', function (Blueprint $table) {
            $table->id();
            $table->string('module_name');
            $table->string('role_name');
            $table->string('rule_type');
            $table->json('rule_config');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Workflow Approval Chains
        Schema::create('approval_chains', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name');
            $table->string('module_name');
            $table->integer('step_order');
            $table->string('approver_role');
            $table->json('conditions')->nullable();
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        // Configuration History
        Schema::create('config_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changed_by')->constrained('users');
            $table->string('config_type');
            $table->string('module_name');
            $table->json('old_value')->nullable();
            $table->json('new_value');
            $table->text('change_reason')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_history');
        Schema::dropIfExists('approval_chains');
        Schema::dropIfExists('visibility_rules');
        Schema::dropIfExists('module_permissions');
    }
};
