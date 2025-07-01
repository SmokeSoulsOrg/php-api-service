<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PornstarThumbnail;
use App\Models\Pornstar;

class PornstarThumbnailFactory extends Factory
{
    protected $model = PornstarThumbnail::class;

    public function definition(): array
    {
        return [
            'pornstar_id' => Pornstar::factory(),
            'type' => $this->faker->randomElement(['pc', 'mobile', 'tablet']),
            'width' => 234,
            'height' => 344,
        ];
    }
}
