<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PornstarThumbnail;
use App\Models\PornstarThumbnailUrl;

class PornstarThumbnailUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PornstarThumbnail::all()->each(function ($thumbnail) {
            PornstarThumbnailUrl::factory()->count(1)->create([
                'thumbnail_id' => $thumbnail->id,
            ]);
        });
    }
}
