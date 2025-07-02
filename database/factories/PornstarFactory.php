<?php

namespace Database\Factories;

use App\Models\Pornstar;
use Illuminate\Database\Eloquent\Factories\Factory;

class PornstarFactory extends Factory
{
    protected $model = Pornstar::class;

    public function definition(): array
    {
        return [
            'external_id' => $this->faker->uuid,
            'name' => $this->faker->name,
            'link' => $this->faker->url,
            'license' => 'REGULAR',
            'wl_status' => $this->faker->boolean,
            'hair_color' => $this->faker->safeColorName,
            'ethnicity' => $this->faker->randomElement(['White', 'Black', 'Asian', 'Latina']),
            'has_tattoos' => $this->faker->boolean,
            'has_piercings' => $this->faker->boolean,
            'breast_size' => $this->faker->numberBetween(30, 40),
            'breast_type' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'DD']),
            'gender' => 'female',
            'orientation' => $this->faker->randomElement(['straight', 'lesbian', 'bisexual']),
            'age' => $this->faker->numberBetween(18, 55),
            'subscriptions' => $this->faker->numberBetween(1000, 50000),
            'monthly_searches' => $this->faker->numberBetween(10000, 1000000),
            'views' => $this->faker->numberBetween(100000, 10000000),
            'videos_count' => $this->faker->numberBetween(1, 100),
            'premium_videos_count' => $this->faker->numberBetween(0, 50),
            'white_label_video_count' => $this->faker->numberBetween(0, 75),
            'rank' => $this->faker->numberBetween(1, 5000),
            'rank_premium' => $this->faker->numberBetween(1, 5000),
            'rank_wl' => $this->faker->numberBetween(1, 5000),
        ];
    }
}
