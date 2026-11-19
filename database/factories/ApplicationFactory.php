<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company' => fake()->company(),
            'role' => fake()->randomElement(['Junior PHP Developer', 'Laravel Developer', 'Backend Engineer', 'Full Stack Developer']),
            'location' => fake()->city(),
            'url' => fake()->url(),
            'salary' => null,
            'status' => ApplicationStatus::Wishlist,
            'position' => 0,
            'applied_at' => null,
            'notes' => null,
        ];
    }

    public function status(ApplicationStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
