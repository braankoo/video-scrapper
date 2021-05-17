<?php

namespace Database\Seeders;

use App\Models\Episode;

use App\Models\Video;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatsSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Video::all()->each(function ($link) {
            $period = CarbonPeriod::create('2021-01-01', '2021-10-01')->toArray();

            $data = array_map(function ($date) use ($link, &$now) {
                $timestamp = Carbon::parse($date);

                return [
                    'video_id'   => $link->id,
                    'views'      => rand(1, 400),
                    'created_at' => $timestamp->format('Y-m-d H:m:s')
                ];
            }, $period);
            DB::table('stats')->insert($data);

        });
    }
}
