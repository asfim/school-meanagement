<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'full_name_en',
        'full_name_native',
        'dob',
        'gender',
        'parent_name',
        'parent_mobile',
        'permanent_address',
        'current_address',
        'tuition_fee',
        'program_name',
        'signature_path',
        'section',
        'roll_number',
        'admission_date',
        'blood_group',
        'photo_path',
        'emergency_contact',
        'status',
        'semester_id',
    ];

    /**
     * Get the casts structure.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'admission_date' => 'date',
            'tuition_fee' => 'decimal:2',
            'semester_id' => 'integer',
        ];
    }

    /**
     * Get the exam results for the student.
     *
     * @return HasMany<ExamResult, $this>
     */
    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * Get the fee payments for the student.
     *
     * @return HasMany<FeePayment, $this>
     */
    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Get the student's current semester.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the student's latest exam result.
     */
    public function latestResult(): HasOne
    {
        return $this->hasOne(ExamResult::class)->latestOfMany();
    }
}
