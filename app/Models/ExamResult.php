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
}
