<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Episode;
use App\Models\Language;
use App\Models\Video;
use App\Models\Series;
use App\Models\Tube;
use Illuminate\Database\Seeder;

class SeriesSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Series::factory()->count(20)->afterCreating(function ($series) {
            $series->episodes()->saveMany(

                Episode::factory()->count(15)->afterCreating(
                    function ($episode) {
                        $episode->actors()->sync(Actor::inRandomOrder()->limit(5)->get());
                        $episode->videos()->saveMany(
                            Video::factory()->count(4)->create(
                                [
                                    'episode_id' => $episode->id, 'tube_id' => Tube::inRandomOrder()->first()
                                ]
                            ));
                    }
                )->create([ 'series_id' => $series->id, 'language_id' => Language::inRandomOrder()->first() ]));

        })->create();
    }
}
