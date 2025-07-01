<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\PornstarAlias;
use App\Models\Pornstar;

class PornstarAliasFactory extends Factory
{
    protected $model = PornstarAlias::class;

    public function definition(): array
    {
        return [
            'pornstar_id' => Pornstar::factory(),
            'alias' => $this->faker->userName,
        ];
    }
}
