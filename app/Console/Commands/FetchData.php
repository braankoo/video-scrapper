<?php

namespace App\Console\Commands;

use App\Jobs\GetViews;
use App\Models\Video;
use Illuminate\Console\Command;

class FetchData extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Views for all videos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Video::each(function ($video) {
            GetViews::dispatch($video)->delay(now()->addMicroseconds(50000));
        });
    }
}
