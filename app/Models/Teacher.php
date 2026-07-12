<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'teacher_id',
        'full_name',
        'dob',
        'mobile',
        'email',
        'qualifications',
        'subjects',
        'classes',
        'date_of_joining',
        'designation',
        'salary_structure',
        'address',
        'photo_path',
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
            'date_of_joining' => 'date',
            'subjects' => 'array',
            'classes' => 'array',
            'salary_structure' => 'array',
        ];
    }

    /**
     * Get the attendance history for this teacher.
     *
     * @return HasMany<TeacherAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }
}
