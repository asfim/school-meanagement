<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeePayment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fee_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'fee_month',
        'amount_due',
        'amount_paid',
        'discount',
        'payment_date',
        'payment_method',
        'receipt_number',
        'status',
        'remarks',
    ];

    /**
     * Get the casts structure.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    /**
     * Get the student that owns the fee payment.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the transactions for the fee payment.
     *
     * @return HasMany<FeePaymentTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(FeePaymentTransaction::class);
    }
}
