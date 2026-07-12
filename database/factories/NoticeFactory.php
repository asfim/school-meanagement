<?php

namespace Database\Factories;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    protected $model = Notice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(4),
            'category' => fake()->randomElement(['exam', 'holiday', 'event', 'general', 'admission', 'urgent']),
            'attachment_path' => null,
            'publish_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'expiry_date' => fake()->optional(0.7)->dateTimeBetween('+1 week', '+2 months')?->format('Y-m-d'),
            'target_audience' => fake()->randomElement(['all', 'students', 'teachers', 'parents']),
            'posted_by' => 'Principal Office',
            'status' => 'active',
        ];
    }
}
