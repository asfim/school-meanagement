<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Computer Science', 'Electrical Engineering', 'Business Administration', 'Mathematics']).' '.fake()->randomElement(['I', 'II', '']),
            'code' => fake()->unique()->regexify('[A-Z]{3}'),
            'description' => fake()->sentence(),
            'duration_years' => fake()->randomElement([2, 3, 4, 5]),
        ];
    }
}
