<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->unsignedBigInteger('video_id');
            $table->bigInteger('views');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('video_id')->references('id')->on('videos')->cascadeOnDelete();
            $table->primary([ 'created_at', 'video_id' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stats');
    }
}
