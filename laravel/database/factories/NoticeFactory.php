<?php

namespace Database\Factories;

use App\Enums\NoticeCategory;
use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(5), '.'),
            'body' => fake()->paragraphs(3, true),
            'category' => fake()->randomElement(NoticeCategory::cases()),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /** State: a draft that is not visible yet. */
    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }
}
