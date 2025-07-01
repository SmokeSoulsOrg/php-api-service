<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pornstar;

class PornstarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pornstar::factory()->count(10)->create();
    }
}
