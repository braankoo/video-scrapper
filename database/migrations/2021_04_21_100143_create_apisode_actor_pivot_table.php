<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApisodeActorPivotTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('episode_actor_pivot', function (Blueprint $table) {
            $table->unsignedBigInteger('episode_id');
            $table->unsignedBigInteger('actor_id');
            $table->foreign('episode_id')->references('id')->on('episodes')->cascadeOnDelete();
            $table->foreign('actor_id')->references('id')->on('actors')->cascadeOnDelete();
            $table->unique([ 'episode_id', 'actor_id' ]);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('episode_actor_pivot');
    }
}
