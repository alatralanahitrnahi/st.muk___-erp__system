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
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_fee_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_installment_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_mode', ['CASH', 'BANK', 'ONLINE', 'DD']);
            $table->string('receipt_no')->nullable();
            $table->date('payment_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
