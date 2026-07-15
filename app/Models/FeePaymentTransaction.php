<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'fee_payment_transactions';

    protected $fillable = [
        'fee_payment_id',
        'payment_date',
        'tuition_fee',
        'discount_type',
        'discount_value',
        'discount_amount',
        'net_payable_amount',
        'amount_paid',
        'remaining_due',
        'status_after_payment',
        'receipt_number',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'tuition_fee' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_payable_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'remaining_due' => 'decimal:2',
        ];
    }

    /**
     * Get the fee payment that owns this transaction.
     */
    public function feePayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class);
    }
}
