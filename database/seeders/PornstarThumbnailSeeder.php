<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pornstar;
use App\Models\PornstarThumbnail;

class PornstarThumbnailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pornstar::all()->each(function ($pornstar) {
            PornstarThumbnail::factory()->count(2)->create([
                'pornstar_id' => $pornstar->id,
            ]);
        });
    }
}
