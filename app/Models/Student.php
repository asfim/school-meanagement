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

    /**
     * Calculate CGPA for the student.
     */
    public function calculateCgpa(): float
    {
        $examResults = $this->examResults;
        if ($examResults->isEmpty()) {
            return 0.00;
        }

        // Group by semester_id
        $groupedHistory = $examResults->groupBy('semester_id');
        $semesterCount = $groupedHistory->count();

        if ($semesterCount >= 8) {
            $totalFinalGpa = 0.00;
            $finalExamCount = 0;

            foreach ($groupedHistory as $semesterId => $exams) {
                // Find final exam in these exams
                $finalExam = $exams->first(function ($res) {
                    return $res->semesterExam && $res->semesterExam->is_final;
                });

                if ($finalExam) {
                    $totalFinalGpa += (float) $finalExam->gpa;
                    $finalExamCount++;
                } else {
                    // Fall back to latest exam of that semester
                    $lastExam = $exams->sortBy('id')->last();
                    if ($lastExam) {
                        $totalFinalGpa += (float) $lastExam->gpa;
                        $finalExamCount++;
                    }
                }
            }

            return $finalExamCount > 0 ? round($totalFinalGpa / $finalExamCount, 2) : 0.00;
        } else {
            // Show last exam's GPA
            $lastExam = $examResults->sortBy('id')->last();

            return $lastExam ? round((float) $lastExam->gpa, 2) : 0.00;
        }
    }

    /**
     * Calculate Grade Letter for the CGPA.
     */
    public function calculateCgpaGrade(float $cgpa): string
    {
        if ($cgpa <= 0) {
            return 'N/A';
        }
        if ($cgpa >= 4.00) {
            return 'A+';
        }
        if ($cgpa >= 3.50) {
            return 'A';
        }
        if ($cgpa >= 3.00) {
            return 'B';
        }
        if ($cgpa >= 2.50) {
            return 'C';
        }
        if ($cgpa >= 2.00) {
            return 'D';
        }

        return 'F';
    }
}
