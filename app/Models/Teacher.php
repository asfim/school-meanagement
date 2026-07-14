<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'full_name',
        'dob',
        'mobile',
        'email',
        'qualifications',
        'subjects',
        'date_of_joining',
        'designation',
        'salary_structure',
        'address',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'date_of_joining' => 'date',
            'subjects' => 'array',
            'salary_structure' => 'array',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class);
    }
}
