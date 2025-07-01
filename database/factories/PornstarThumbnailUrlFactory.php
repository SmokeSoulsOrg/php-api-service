<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PornstarThumbnailUrl;
use App\Models\PornstarThumbnail;

class PornstarThumbnailUrlFactory extends Factory
{
    protected $model = PornstarThumbnailUrl::class;

    public function definition(): array
    {
        return [
            'thumbnail_id' => PornstarThumbnail::factory(),
            'url' => $this->faker->imageUrl(234, 344),
            'local_path' => null,
        ];
    }
}
