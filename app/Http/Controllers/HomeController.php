<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller {


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Collection
     */
    public function viewsDailyChart(Request $request): \Illuminate\Support\Collection
    {

        return \DB::table('stats')
            ->selectRaw("SUM(views) as views, DATE_FORMAT(stats.created_at, '%Y-%m-%d') as date")
            ->when(!empty($request->input('series')), function ($q) use ($request) {
                $q->join('videos', 'stats.video_id', '=', 'videos.id');
                $q->join('episodes', 'videos.episode_id', '=', 'episodes.id');
                $q->whereIn('episodes.series_id', $request->input('series'));
            })
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
            ->get();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function viewsMonthlyChart(Request $request): array
    {

        $data = \DB::table('stats')
            ->selectRaw("SUM(views) as views, DATE_FORMAT(created_at, '%Y-%m-%d') as date")
            ->where(DB::raw('DAY(created_at)'), '=', '1')
            ->groupBy(DB::raw('DATE_FORMAT(created_at ,"%Y-%m-%d")'))
            ->get();

        $transformedData = [];

        for ( $i = 0; $i < count($data); $i ++ )
        {

            if ($i == 0)
            {
                $not1st = \DB::table('stats')
                    ->selectRaw("SUM(views) as views, DATE_FORMAT(created_at, '%Y-%m-%d') as date")
                    ->whereDate('created_at', '<', $data[$i]->date)
                    ->groupBy(DB::raw('DATE_FORMAT(created_at ,"%Y-%m-%d")'))
                    ->get();

                if ($not1st->count())
                {
                    $transformedData[] = [
                        'views' => $data[$i]->views - $not1st->first()->views,
                        'date'  => $data[$i]->date
                    ];
                } else
                {
                    $transformedData[] = [
                        'views' => $data[$i]->views,
                        'date'  => $data[$i]->date
                    ];
                }

                if ($i == count($data) - 1)
                {
                    $afterCurrent = \DB::table('stats')
                        ->selectRaw("SUM(views) as views, DATE_FORMAT(created_at, '%Y-%m-%d') as date")
                        ->whereDate('created_at', '>', $data[$i]->date)
                        ->groupBy(DB::raw('DATE_FORMAT(created_at ,"%Y-%m-%d")'))
                        ->get();

                    if ($afterCurrent->count())
                    {
                        $transformedData[] = [
                            'views' => $afterCurrent->last()->views - $data[$i]->views,
                            'date'  => $afterCurrent->last()->date
                        ];
                    }
                }


            } else if ($i == count($data) - 1)
            {

                $transformedData[] = [
                    'views' => $data[$i]->views - $data[$i - 1]->views,
                    'date'  => $data[$i]->date
                ];

                $afterCurrent = \DB::table('stats')
                    ->selectRaw("SUM(views) as views, DATE_FORMAT(created_at, '%Y-%m-%d') as date")
                    ->whereDate('created_at', '>', $data[$i]->date)
                    ->groupBy(DB::raw('DATE_FORMAT(created_at ,"%Y-%m-%d")'))
                    ->get();

                if ($afterCurrent->count())
                {
                    $transformedData[] = [
                        'views' => $afterCurrent->last()->views - $data[$i]->views,
                        'date'  => $afterCurrent->last()->date
                    ];
                }
            } else
            {
                $transformedData[] = [
                    'views' => $data[$i]->views - $data[$i - 1]->views,
                    'date'  => $data[$i]->date
                ];
            }
            if ($i == count($data) - 1)
            {
                break;
            }
        }

        return $transformedData;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topSeries(Request $request): JsonResponse
    {
        $stats = DB::table('stats')
            ->selectRaw('series.name as name, SUM(views) as views, date(stats.created_at) as created_at')
            ->join('videos', 'videos.id', 'stats.video_id')
            ->join('episodes', 'videos.episode_id', '=', 'episodes.id')
            ->join('series', 'episodes.series_id', '=', 'series.id')
            ->when(!empty($request->input('series')), function ($q) use ($request) {
                $q->whereIn('series.id', $request->input('series'));
            })
            ->when(!empty($request->input('actors')), function ($q) use ($request) {
                $q->join('episode_actor_pivot', 'episodes.id', '=', 'episode_actor_pivot.episode_id');
                $q->whereIn('episode_actor_pivot.actor_id', $request->input('actors'));
            })
            ->when(!empty($request->input('languages')), function ($q) use ($request) {
                $q->whereIn('episodes.language_id', $request->input('languages'));
            })
            ->groupBy([ 'series.id', DB::raw('DATE(stats.created_at)') ])
            ->orderBy(DB::raw('sum(views)'), 'DESC')
            ->get();


        return $this->groupByNameAndChechDate($request, $stats);

    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topEpisodes(Request $request): JsonResponse
    {
        $stats = DB::table('stats')
            ->selectRaw('episodes.name as name, SUM(views) as views,date(stats.created_at) as created_at')
            ->join('videos', 'videos.id', 'stats.video_id')
            ->join('episodes', 'videos.episode_id', '=', 'episodes.id')
            ->when(!empty($request->input('series')), function ($q) use ($request) {
                $q->whereIn('episodes.series_id', $request->input('series'));
            })
            ->when(!empty($request->input('actors')), function ($q) use ($request) {
                $q->join('episode_actor_pivot', 'episodes.id', '=', 'episode_actor_pivot.episode_id');
                $q->whereIn('episode_actor_pivot.actor_id', $request->input('actors'));
            })
            ->when(!empty($request->input('languages')), function ($q) use ($request) {
                $q->whereIn('episodes.language_id', $request->input('languages'));
            })
            ->groupBy([ 'episodes.id', DB::raw('DATE(stats.created_at)') ])
            ->orderBy(DB::raw('sum(views)'), 'DESC')
            ->get();


        return $this->groupByNameAndChechDate($request, $stats);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topActors(Request $request): JsonResponse
    {

        $stats = DB::table('stats')
            ->selectRaw('actors.name as name,SUM(views) as views, DATE(stats.created_at) as created_at')
            ->join('videos', 'videos.id', 'stats.video_id')
            ->join('episodes', 'videos.episode_id', '=', 'episodes.id')
            ->join('episode_actor_pivot', 'episodes.id', '=', 'episode_actor_pivot.episode_id')
            ->join('actors', 'episode_actor_pivot.actor_id', '=', 'actors.id')
            ->when(!empty($request->input('series')), function ($q) use ($request) {
                $q->whereIn('episodes.series_id', $request->input('series'));
            })
            ->when(!empty($request->input('actors')), function ($q) use ($request) {
                $q->whereIn('actors.id', $request->input('actors'));
            })
            ->when(!empty($request->input('languages')), function ($q) use ($request) {
                $q->whereIn('episodes.language_id', $request->input('languages'));
            })
            ->where('actors.gender', '=', $request->input('gender'))
            ->groupBy([ 'actors.id', DB::raw('DATE(stats.created_at)') ])
            ->orderBy(DB::raw('sum(views)'), 'DESC')
            ->get();


        return $this->groupByNameAndChechDate($request, $stats);
    }

    /**
     * @param \Illuminate\Support\Collection $stats
     * @return \Illuminate\Support\Collection
     */
    private function transformForFront(Collection $stats): Collection
    {

        return $stats->transform(function ($single, $name) {
            $obj = new \stdClass();
            if ($single->count() > 1)
            {

                $obj->name = $name;
                $obj->views = $single->first()->views - $single->last()->views;

            } else
            {
                $obj->name = $name;
                $obj->views = $single->first()->views;

            }

            return $obj;
        })->values();


    }

    /**
     * @param \Illuminate\Support\Collection $stats
     * @return \Illuminate\Support\Collection
     */
    private function transformForFrontWithoutDate(Collection $stats): Collection
    {
        $last = DB::table('stats')->selectRaw('DATE(created_at) as created_at')->latest(DB::raw('DATE(created_at)'))->first();

        return $stats->filter(function ($row) use ($last) {
            return $row->created_at == $last->created_at;
        })->transform(function ($single, $name) {
            $obj = new \stdClass();
            $obj->name = $single->name;
            $obj->views = $single->views;

            return $obj;
        });

    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Support\Collection $stats
     * @return \Illuminate\Http\JsonResponse
     */
    private function groupByNameAndChechDate(Request $request, Collection $stats): JsonResponse
    {
        if (!empty($request->input('date')))
        {
            $stats = $stats->filter(function ($row) use ($request) {
                return $row->created_at >= $request->input('date');
            });
            $stats = $stats->groupBy('name');

            $stats = $this->transformForFront($stats);

        } else
        {
            $stats = $this->transformForFrontWithoutDate($stats);

        }

        $data = $stats->sortByDesc('views')->take(10)->values();

        $totalViews = 0;
        foreach ( $data as $single )
        {
            $totalViews += $single->views;
        }

        return response()->json([ 'data' => $data, 'total' => $totalViews ], JsonResponse::HTTP_OK);
    }
}
