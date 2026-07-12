<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => 'TCH-' . date('Y') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'full_name' => fake()->name(),
            'dob' => fake()->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
            'mobile' => '01' . fake()->numberBetween(300000000, 999999999),
            'email' => fake()->unique()->safeEmail(),
            'qualifications' => fake()->randomElement([
                'B.Sc. in Mathematics, M.Sc. in Applied Mathematics',
                'B.A. in English literature, M.A. in English',
                'B.Sc. in Physics, B.Ed.',
                'B.Sc. in Chemistry, M.Sc.',
                'M.A. in History, B.Ed.'
            ]),
            'subjects' => fake()->randomElements(['Mathematics', 'English', 'Physics', 'Chemistry', 'Biology', 'History', 'Geography', 'Social Science'], 2),
            'classes' => fake()->randomElements(['Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10'], 3),
            'date_of_joining' => fake()->dateTimeBetween('-10 years', '-1 years')->format('Y-m-d'),
            'designation' => fake()->randomElement(['Assistant Teacher', 'Senior Teacher', 'Lecturer', 'Head of Department']),
            'salary_structure' => [
                'base_salary' => fake()->numberBetween(25000, 50000),
                'allowances' => fake()->numberBetween(2000, 8000),
                'deductions' => fake()->numberBetween(1000, 3000),
            ],
            'address' => fake()->address(),
            'photo_path' => null,
        ];
    }
}
