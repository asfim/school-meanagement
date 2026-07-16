<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'exam_results';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'exam_name',
        'program_name',
        'section',
        'marks',
        'gpa',
        'grade',
        'pass_status',
        'merit_position',
        'remarks',
        'semester_id',
        'semester_exam_id',
    ];

    /**
     * Get the casts structure.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'marks' => 'array',
            'gpa' => 'decimal:2',
            'merit_position' => 'integer',
            'semester_id' => 'integer',
            'semester_exam_id' => 'integer',
        ];
    }

    /**
     * Get the student that owns the exam result.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the semester associated with the exam result.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the semester exam associated with the exam result.
     */
    public function semesterExam(): BelongsTo
    {
        return $this->belongsTo(SemesterExam::class);
    }
}
