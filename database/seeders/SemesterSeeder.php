<?php

namespace Database\Seeders;

use App\Models\Semester;
use App\Models\SemesterExam;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            '1st Semester',
            '2nd Semester',
            '3rd Semester',
            '4th Semester',
            '5th Semester',
            '6th Semester',
            '7th Semester',
            '8th Semester',
        ];

        foreach ($semesters as $index => $name) {
            $semester = Semester::firstOrCreate(
                ['name' => $name],
                ['sort_order' => $index + 1]
            );

            // Seed default exams
            $exams = [
                ['name' => '1st Term', 'is_final' => false, 'sort_order' => 1],
                ['name' => '2nd Term', 'is_final' => false, 'sort_order' => 2],
                ['name' => '3rd Term', 'is_final' => false, 'sort_order' => 3],
                ['name' => 'Final Semester Exam', 'is_final' => true, 'sort_order' => 4],
            ];

            foreach ($exams as $exam) {
                SemesterExam::firstOrCreate(
                    [
                        'semester_id' => $semester->id,
                        'name' => $exam['name'],
                    ],
                    [
                        'is_final' => $exam['is_final'],
                        'sort_order' => $exam['sort_order'],
                    ]
                );
            }
        }
    }
}
