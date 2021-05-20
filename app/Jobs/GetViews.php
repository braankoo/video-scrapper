<?php

namespace App\Jobs;

use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Class GetViews
 * @package App\Jobs
 */
class GetViews implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\Video
     */
    public Video $video;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->video->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $str = curl_exec($curl);
        curl_close($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


        DB::table('stats')->whereDate('created_at', Carbon::today()->format('Y-m-d'))->where('video_id', '=', $this->video->id)->delete();

        if ($http_status == 200)
        {

            $html = str_get_html($str);
            $numberOfViews = $this->parse($html);


            if (is_null($numberOfViews))
            {
                $this->insertNulAndMarkVideoAsIncorrect();

            }

            if ($this->video->errors == 'true')
            {
                $this->video->errors = 'false';
                $this->video->save();
            }

            DB::table('stats')->insert(
                [
                    'video_id' => $this->video->id,
                    'views'    => $numberOfViews
                ]
            );
        } else
        {
            $this->insertNulAndMarkVideoAsIncorrect();

        }

    }

    /**
     * @param \simple_html_dom $html
     * @return int
     * @throws \Exception
     */
    private function parse(\simple_html_dom $html): int
    {
        switch ( $this->video->tube->name )
        {
            case 'xnxx.com':

                $data = $html->find('span[class=metadata]');
                if (empty($data))
                {
                    $this->insertNulAndMarkVideoAsIncorrect();
                }
                $data = explode('-', $data[0]->plaintext);

                return (int) str_replace([ ' ', ',', '.' ], '', last($data));
            case 'xhamster.com':
                $data = $html->find('div[class=header-icons] > span');
                if (empty($data))
                {
                    $this->insertNulAndMarkVideoAsIncorrect();
                }

                return (int) str_replace([ ' ', ',', '.' ], '', $data[0]->plaintext);
            case 'pornhub.com':

                $data = $html->find('div[class=views] > span[class=count]');

                if (empty($data))
                {
                    $this->insertNulAndMarkVideoAsIncorrect();
                }

                return (int) str_replace([ ' ', ',', '.' ], '', $data[0]->plaintext);
            case 'xvideos.com':
                $data = $html->find('span[class=views-full] > strong[id=nb-views-number]');
                if (empty($data))
                {
                    $this->insertNulAndMarkVideoAsIncorrect();
                }

                return (int) str_replace([ ' ', ',', '.' ], '', $data[0]->plaintext);
            default:
                throw new \Exception('Unknown Tube');
        }
    }

    /**
     *
     */
    private function insertNulAndMarkVideoAsIncorrect(): void
    {
        DB::table('stats')->insert(
            [
                'video_id' => $this->video->id,
                'views'    => null
            ]
        );
        $this->video->errors = 'true';
        $this->video->save();
        $this->fail('Error While Fetching Data. Video id: ' . $this->video->id . ' Date:' . Carbon::now()->format('Y-m-d'));
    }

    public function fail($exception = null)
    {
        $this->insertNulAndMarkVideoAsIncorrect();
    }
}
