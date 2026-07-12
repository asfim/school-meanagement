<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'student_id' => 'STU-'.date('Y').'-'.fake()->unique()->numberBetween(1000, 9999),
            'full_name_en' => fake()->name($gender),
            'full_name_native' => fake()->name($gender), // using native/standard name
            'dob' => fake()->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'gender' => $gender,
            'parent_name' => fake()->name('male'),
            'parent_mobile' => '01'.fake()->numberBetween(300000000, 999999999),
            'permanent_address' => fake()->address(),
            'current_address' => fake()->address(),
            'class' => fake()->randomElement(['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'Class 11', 'Class 12']),
            'section' => fake()->randomElement(['A', 'B', 'C']),
            'roll_number' => fake()->numberBetween(1, 60),
            'admission_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'photo_path' => null,
            'emergency_contact' => '01'.fake()->numberBetween(300000000, 999999999),
            'status' => 'active',
        ];
    }
}
