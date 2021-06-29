<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller {


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Collection
     */
    public function viewsDailyChart(Request $request): \Illuminate\Support\Collection
    {

        return \DB::table('stats')
            ->selectRaw("SUM(views) as views, DATE_FORMAT(created_at, '%Y-%m-%d') as date")
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
            ->selectRaw("SUM(views) as views, DATE_FORMAT(created_at, '%Y-%m') as date")
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get();
        $transformedData = [];
        for ( $i = 0; $i < count($data); $i ++ )
        {
            if ($i == 0)
            {
                continue;
            } else
            {
                $transformedData[] = [
                    'views' => $data[$i]->views - $data[$i - 1]->views,
                    'date'  => $data[$i]->date
                ];
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
        $data = DB::table('stats')
            ->selectRaw('series.name as series, SUM(views) as views')
            ->join('videos', 'videos.id', 'stats.video_id')
            ->join('episodes', 'videos.episode_id', '=', 'episodes.id')
            ->join('series', 'episodes.series_id', '=', 'series.id')
            ->when(!empty($request->input('date')), function ($q) use ($request) {
                $q->whereDate('stats.created_at', '=', $request->input('date'));
            })
            ->when(empty($request->input('date')), function ($q) use ($request) {
                $q->whereDate('stats.created_at', '=', Carbon::now());
            })
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
            ->groupBy('series.id')
            ->orderBy(DB::raw('sum(views)'), 'DESC')
            ->take(10)
            ->get();
        $totalViews = 0;
        foreach ( $data as $single )
        {
            $totalViews += $single->views;
        }

        return response()->json([ 'data' => $data, 'total' => $totalViews ], JsonResponse::HTTP_OK);

    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topEpisodes(Request $request): JsonResponse
    {
        $data = DB::table('stats')
            ->selectRaw('episodes.name as episode, SUM(views) as views')
            ->join('videos', 'videos.id', 'stats.video_id')
            ->join('episodes', 'videos.episode_id', '=', 'episodes.id')
            ->when(!empty($request->input('date')), function ($q) use ($request) {
                $q->whereDate('stats.created_at', '=', $request->input('date'));
            })
            ->when(empty($request->input('date')), function ($q) use ($request) {
                $q->whereDate('stats.created_at', '=', Carbon::now());
            })
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
            ->groupBy('episodes.id')
            ->orderBy(DB::raw('sum(views)'), 'DESC')
            ->take(10)
            ->get();
        $totalViews = 0;
        foreach ( $data as $single )
        {
            $totalViews += $single->views;
        }

        return response()->json([ 'data' => $data, 'total' => $totalViews ], JsonResponse::HTTP_OK);
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Support\Collection
     */
    public function topActors(Request $request): \Illuminate\Support\Collection
    {

        return DB::table('stats')
            ->selectRaw('actors.name,SUM(views) as views')
            ->join('videos', 'videos.id', 'stats.video_id')
            ->join('episodes', 'videos.episode_id', '=', 'episodes.id')
            ->join('episode_actor_pivot', 'episodes.id', '=', 'episode_actor_pivot.episode_id')
            ->join('actors', 'episode_actor_pivot.actor_id', '=', 'actors.id')
            ->when(!empty($request->input('date')), function ($q) use ($request) {
                $q->whereDate('stats.created_at', '=', $request->input('date'));
            })
            ->when(empty($request->input('date')), function ($q) use ($request) {
                $q->whereDate('stats.created_at', '=', Carbon::now());
            })
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
            ->groupBy('actors.id')
            ->orderBy(DB::raw('sum(views)'), 'DESC')
            ->take(10)
            ->get();
    }
}
