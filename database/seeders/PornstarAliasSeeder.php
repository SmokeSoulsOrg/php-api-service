<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pornstar;
use App\Models\PornstarAlias;

class PornstarAliasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pornstar::all()->each(function ($pornstar) {
            PornstarAlias::factory()->count(3)->create([
                'pornstar_id' => $pornstar->id,
            ]);
        });
    }
}
