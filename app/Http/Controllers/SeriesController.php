<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FilterTrait;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeriesController extends Controller {

    use FilterTrait;

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->has('search'))
        {
            return response()->json(Series::whereRaw('name LIKE ?', [ '%' . $request->input('search') . '%' ])->simplePaginate(10, [ '*' ]));
        }

        return response()->json(Series::withCount('episodes')->paginate(10, [ 'id', 'name' ], 'page', $request->input('page')), JsonResponse::HTTP_OK);
    }


    /**
     * @param \App\Models\Series $series
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Series $series): JsonResponse
    {
        return response()->json($series, JsonResponse::HTTP_OK);
    }

    /**
     * @param \App\Models\Series $series
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Series $series, Request $request): JsonResponse
    {

        $filters = json_decode($request->input('filter'));

        $subQuery = DB::table('actors')->selectRaw('GROUP_CONCAT(name) as actors,episode_id')
            ->join('episode_actor_pivot', 'actors.id', '=', 'episode_actor_pivot.actor_id');
        if (!empty($filters->actors))
        {
            $subQuery->whereIn('actors.id', $filters->actors);
        }
        $subQuery->groupBy('episode_id');

        $query = $series->episodes()->select(
            [
                'episodes.name as name', 'episodes.id as id', 'actors', 'languages.name as languages.name', DB::raw('SUM(views) as views') ])
            ->join('languages', 'episodes.language_id', '=', 'languages.id')
            ->joinSub(
                $subQuery,
                'actors', function ($join) {
                $join->on('episodes.id', '=', 'actors.episode_id');
            })
            ->leftJoin('videos', 'episodes.id', '=', 'videos.episode_id')
            ->leftJoin('stats', 'videos.id', '=', 'stats.video_id');


        $query = $this->languageFilter($query, $filters->languages);


        if (!empty($filters->end_date))
        {
            $query->whereDate('stats.created_at', '=', $filters->end_date);
        } else if (!empty($date->start_date))
        {
            $query->whereDate('stats.created_at', '=', $filters->start_date);
        }

        return response()->json(
            $query->groupBy('episodes.id')
                ->orderBy((!empty($request->input('sortBy')) ? $request->input('sortBy') : 'episodes.id'), ($request->input('sortDesc') == 'true' ? 'asc' : 'desc'))
                ->paginate('10', [ '*' ], 'page', $request->input('page'))

            , JsonResponse::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([ 'name' => 'required' ]);
        Series::insert([ 'name' => $request->input('name') ]);

        return response()->json([ 'message' => 'Successfuly Created' ], JsonResponse::HTTP_CREATED);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Series $series
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Series $series): JsonResponse
    {
        $request->validate([ 'name' => 'required|unique:series' ]);
        $series->name = $request->input('name');
        $series->save();

        return response()->json([ 'message' => 'Successfuly updated' ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Series $series
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Series $series): JsonResponse
    {
        $series->delete();

        return response()->json([ 'message' => 'Successfuly deleted' ], JsonResponse::HTTP_OK);
    }
}
