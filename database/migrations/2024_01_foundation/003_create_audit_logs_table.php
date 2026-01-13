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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // User who made the change (nullable for system actions)
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Polymorphic relationship fields
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            
            // Event type
            $table->enum('event', ['created', 'updated', 'deleted']);
            
            // Data changes
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Request information
            $table->string('ip_address', 45)->nullable(); // Support IPv4 and IPv6
            $table->text('user_agent')->nullable();
            
            // Timestamp (no updated_at needed)
            $table->timestamp('created_at');
            
            // Indexes
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('event');
            $table->index('created_at');
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};