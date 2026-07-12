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
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('fee_month'); // e.g. "2026-07"
            $table->decimal('amount_due', 8, 2);
            $table->decimal('amount_paid', 8, 2)->default(0.00);
            $table->decimal('discount', 8, 2)->default(0.00);
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable(); // cash, bank, mobile_banking
            $table->string('receipt_number')->unique()->nullable();
            $table->string('status')->default('unpaid'); // unpaid, partial, paid
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'fee_month']);
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
