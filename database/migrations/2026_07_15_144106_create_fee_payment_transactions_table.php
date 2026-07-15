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
        Schema::create('fee_payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_payment_id')->constrained('fee_payments')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('tuition_fee', 8, 2);
            $table->string('discount_type'); // percentage, fixed
            $table->decimal('discount_value', 8, 2)->default(0.00);
            $table->decimal('discount_amount', 8, 2)->default(0.00);
            $table->decimal('net_payable_amount', 8, 2);
            $table->decimal('amount_paid', 8, 2);
            $table->decimal('remaining_due', 8, 2);
            $table->string('status_after_payment');
            $table->string('receipt_number')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payment_transactions');
    }
};
