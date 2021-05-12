<?php

namespace Database\Seeders;

use App\Models\Tube;
use Illuminate\Database\Seeder;

class TubeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Tube::factory()->count(4)->create();
    }
}
