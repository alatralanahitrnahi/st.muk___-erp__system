<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_history', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name', 50);
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->string('from_state', 50);
            $table->string('to_state', 50);
            $table->foreignId('performed_by')->constrained('users');
            $table->text('comment')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
            
            $table->index(['entity_type', 'entity_id']);
            $table->index(['department_id', 'performed_at']);
            $table->index('workflow_name');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('user_id')->constrained('departments');
                $table->index('department_id');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_history');
        
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};
