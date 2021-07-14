<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FilterTrait;
use App\Models\Episode;
use App\Models\Series;
use App\Models\Video;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller {

    use FilterTrait;

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $filters = json_decode($request->input('filter'));
        $format = '%Y-%m-%d';

        if ($request->input('group_by') == 'month')
        {

            $format = '%Y-%m';
        }

        $period = CarbonPeriod::create($filters->date->start_date, $filters->date->end_date);

        $dates = [];
        foreach ( $period as $date )
        {
            $dates[] = $date->format('Y-m-d');
        }
        $dates = (array_values(array_unique($dates)));


        $pagination = Episode::with([ 'series', 'stats' => function ($q) use ($request, $format) {
            $q->selectRaw(DB::raw("episode_id, sum(views) as views, DATE_FORMAT(stats.created_at,'{$format}') as date"));

            if (!empty($filters->date->start_date))
            {
                $q->whereDate('stats.created_at', '>=', $filters->date->start_date);
            }
            if (!empty($filters->date->end_date))
            {
                $q->whereDate('stats.created_at', '<=', $filters->date->end_date);
            }

            $q->groupBy([ 'episode_id', DB::raw("DATE_FORMAT(stats.created_at,'$format')") ]);
        } ]);

        if (!empty($filters->series))
        {

            $pagination->whereHas('series', function ($query) use ($filters) {
                return $query->whereIn('series_id', $filters->series);
            });
        }
        if (!empty($filters->actors))
        {
            $pagination->whereHas('actors', function ($query) use ($filters) {
                return $query->whereIn('actor_id', $filters->actors);
            });
        }
        if (!empty($filters->languages))
        {
            $pagination->whereIn('language_id', $filters->language);

        }

        $pagination = $pagination->paginate(20);

        $itemsTransformed = new Collection();
        foreach ( $pagination->items() as $item )
        {

            if (!$item->stats->count())
            {
                foreach ( $dates as $date )
                {
                    $itemsTransformed->push([ 'date' => $date, 'views' => 0, 'episode' => $item->name, 'series' => $item->series->name ]);
                }
            } else
            {
                foreach ( $dates as $date )
                {

                    if (!$item->stats->contains('date', $date))
                    {
                        $itemsTransformed->push([ 'date' => $date, 'views' => 0, 'episode' => $item->name, 'series' => $item->series->name ]);
                    } else
                    {

                        $itemsTransformed->push([ 'date' => $date, 'views' => (int) $item->stats->firstWhere('date', $date)->views, 'episode' => $item->name, 'series' => $item->series->name ]);

                    }
                }

            }
        }

        $total = $itemsTransformed->groupBy([ 'date' ])->map(function ($url) {
            return $url->sum('views');
        });


        $itemsTransformed = $itemsTransformed->groupBy([ 'series' ])->map(function ($series) {
            return $series->groupBy([ 'episode' ])->map(function ($episode) {

                return $episode->map(function ($data) {
                    return [
                        'date'  => $data['date'],
                        'views' => $data['views']
                    ];
                });
            });
        });


        return new \Illuminate\Pagination\LengthAwarePaginator(
            [
                'series' => $itemsTransformed,
                'total'  => $total
            ],
            $pagination->total(),
            20,
            $pagination->currentPage(),
            [ 'path'  => \Request::url(),
              'query' => [ 'page' => $pagination->currentPage() ]
            ]
        );


    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function episode(Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {

        $filters = json_decode($request->input('filter'));
        $format = '%Y-%m-%d';

        if ($request->input('group_by') == 'month')
        {

            $format = '%Y-%m';
        }

        $period = CarbonPeriod::create($filters->date->start_date, $filters->date->end_date);

        $dates = [];
        foreach ( $period as $date )
        {
            $dates[] = $date->format('Y-m-d');
        }
        $dates = (array_values(array_unique($dates)));

        $pagination = Episode::firstWhere('name', $request->input('name'))->videos()->with([ 'stats' => function ($q) use ($request, $format) {
            $q->selectRaw(DB::raw("video_id, sum(views) as views, DATE_FORMAT(stats.created_at,'{$format}') as date"));

            if (!empty($filters->date->start_date))
            {
                $q->whereDate('stats.created_at', '>=', $filters->date->start_date);
            }
            if (!empty($filters->date->end_date))
            {
                $q->whereDate('stats.created_at', '<=', $filters->date->end_date);
            }

            $q->groupBy([ 'video_id', DB::raw("DATE_FORMAT(stats.created_at,'$format')") ]);
        } ]);

        if (!empty($filters->series))
        {
            $pagination->whereHas('episode', function ($query) use ($filters) {
                return $query->whereIn('series_id', $filters->series);
            });
        }
        if (!empty($filters->actors))
        {
            $pagination->whereHas('episode.actors', function ($query) use ($filters) {
                return $query->whereIn('actor_id', $filters->actors);
            });
        }
        if (!empty($filters->languages))
        {
            $pagination->whereHas('episode', function ($query) use ($filters) {
                return $query->whereIn('language_id', $filters->language);
            });
        }
        $pagination = $pagination->paginate(20);


        $errors = [];
        foreach ( $pagination->items() as $item )
        {
            $errors[$item->url] = $item->errors;
        }
        $ids = [];
        foreach ( $pagination->items() as $item )
        {
            $ids[$item->url] = $item->id;
        }

        $itemsTransformed = new Collection();
        foreach ( $pagination->items() as $item )
        {

            if (!$item->stats->count())
            {
                foreach ( $dates as $date )
                {
                    $itemsTransformed->push([ 'date' => $date, 'views' => 0, 'url' => $item->url ]);
                }
            } else
            {
                foreach ( $dates as $date )
                {
                    if (!$item->stats->contains('date', $date))
                    {
                        $itemsTransformed->push([ 'date' => $date, 'views' => 0, 'url' => $item->url ]);
                    } else
                    {

                        $itemsTransformed->push([ 'date' => $date, 'views' => (int) $item->stats->firstWhere('date', $date)->views, 'url' => $item->url ]);

                    }
                }

            }
        }


        $itemsTransformed = $itemsTransformed->groupBy([ 'url' ])->map(function ($episode) {
            return $episode->map(function ($data) {
                return [
                    'date'  => $data['date'],
                    'views' => $data['views']
                ];
            });
        });

        return new \Illuminate\Pagination\LengthAwarePaginator(
            [
                'videos' => $itemsTransformed,
                'dates'  => $dates,
                'errors' => $errors,
                'ids'    => $ids
            ],
            $pagination->total(),
            $pagination->perPage(),
            $pagination->currentPage(),
            [ 'path'  => \Request::url(),
              'query' => [ 'page' => $pagination->currentPage() ]
            ]
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function update(Request $request): \Illuminate\Http\JsonResponse
    {

        $video = Video::where('url', '=', $request->input('video'))->first();
        DB::table('stats')->whereDate('created_at', $request->input('date'))->where('video_id', '=', $video->id)->delete();

        DB::table('stats')->insert(
            [
                'video_id'   => $video->id,
                'views'      => $request->input('views'),
                'created_at' => $request->input('date')
            ]
        );

        return response()->json([ 'message' => 'Success' ], 200);

    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function csv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filters = json_decode($request->input('filters'));

        $format = '%Y-%m-%d';

        if ($request->input('group_by') == 'month')
        {
            $format = '%Y-%m';
        }
        $query = DB::table('stats')->select('videos.url', DB::raw('IFNULL(sum(views),0) as views'), DB::raw("DATE_FORMAT(stats.created_at,'{$format}') as date"))->join('videos', 'stats.video_id', '=', 'videos.id');
        $query = $this->dateRangeFilter($query, $filters->date);
        $query->groupBy([ DB::raw("DATE_FORMAT(stats.created_at,'{$format}')"), 'videos.id' ]);

        $data = $query->get()->groupBy([ 'date', 'url' ])->map(function ($item) {
            return $item->toArray();
        })->toArray();

        $csv = [];
        $csv[0] = [ 'Videos' ];
        array_push($csv[0], ...array_keys($data));
        $videos = array_values(array_unique(\Arr::flatten(array_values(array_map(function ($url) {
            return array_keys($url);
        }, $data)))));

        foreach ( $videos as $video )
        {
            $csv[] = [ $video ];
        }


        foreach ( $data as $date => $value )
        {
            foreach ( $videos as $video )
            {
                if (in_array($video, array_keys($value)))
                {

                    $csv[array_search($video, $videos) + 1][] = $value[$video][0]->views;
                } else
                {
                    $csv[array_search($video, $videos) + 1][] = 0;
                }
            }
        }


        $callback = function () use ($csv) {
            $FH = fopen('php://output', 'w');
            foreach ( $csv as $row )
            {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return \Response::stream($callback, 200,
            [
                'Cache-Control'         => 'must-revalidate, post-check=0, pre-check=0'
                , 'Content-type'        => 'text/csv'
                , 'Content-Disposition' => 'attachment; filename=stats.csv'
                , 'Expires'             => '0'
                , 'Pragma'              => 'public'
            ]
        );
    }


}
