<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function exams(): HasMany
    {
        return $this->hasMany(SemesterExam::class)->orderBy('sort_order');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
