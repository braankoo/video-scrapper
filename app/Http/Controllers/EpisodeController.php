<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FilterTrait;
use App\Models\Episode;
use App\Models\Series;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class EpisodeController
 * @package App\Http\Controllers
 */
class EpisodeController extends Controller {

    use FilterTrait;

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $filters = json_decode($request->input('filter'));

        $subQuery = DB::table('actors')->selectRaw('GROUP_CONCAT(name) as actors,episode_id')
            ->join('episode_actor_pivot', 'actors.id', '=', 'episode_actor_pivot.actor_id');
        if (!empty($filters->actors))
        {
            $subQuery->whereIn('actors.id', $filters->actors);
        }
        $subQuery->groupBy('episode_id');

        $query = Episode::select([ 'episodes.name as episodes.name', 'series.id as series_id', 'episodes.id as id', 'series.name as series.name', 'actors', 'languages.name as languages.name', DB::raw('SUM(views) as views') ])
            ->join('series', 'episodes.series_id', '=', 'series.id')
            ->join('languages', 'episodes.language_id', '=', 'languages.id')
            ->joinSub(
                $subQuery,
                'actors', function ($join) {
                $join->on('episodes.id', '=', 'actors.episode_id');
            })
            ->leftJoin('videos', 'episodes.id', '=', 'videos.episode_id')
            ->leftJoin('stats', 'videos.id', '=', 'stats.video_id');

        if (!empty($filters->series))
        {
            $query->whereIn('series.id', $filters->series);
        }
        $query = $this->languageFilter($query, $filters->languages);

        $query = $this->dateRangeFilter($query, $filters->date);


        return response()->json(
            $query->groupBy('episodes.id')
                ->orderBy((!empty($request->input('sortBy')) ? $request->input('sortBy') : 'episodes.id'), ($request->input('sortDesc') == 'true' ? 'asc' : 'desc'))
                ->paginate('10', [ '*' ], 'page', $request->input('page'))

            , JsonResponse::HTTP_OK);
    }


    /**
     * @param \App\Models\Series $series
     * @param \App\Models\Episode $episode
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleStats(Series $series, Episode $episode, Request $request): JsonResponse
    {
        $filters = json_decode($request->input('filter'));

        $query = $episode->videos()->select(
            [ 'videos.url as url', 'videos.id as id', DB::raw('SUM(views) as views') ])
            ->leftJoin('stats', 'videos.id', '=', 'stats.video_id');

        if (!empty($filters->date->end_date))
        {
            $query->whereDate('stats.created_at', '=', $filters->date->end_date);
        } else
        {
            $query->whereDate('stats.created_at', '=', $filters->date->start_date);
        }

        return response()->json(
            $query->groupBy('videos.id')
                ->orderBy((!empty($request->input('sortBy')) ? $request->input('sortBy') : 'videos.id'), ($request->input('sortDesc') == 'true' ? 'asc' : 'desc'))
                ->paginate('20', [ '*' ], 'page', $request->input('page'))

            , JsonResponse::HTTP_OK);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Episode::paginate(20, [ 'id', 'name' ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(
            [
                'name'                => 'required',
                'language'            => 'required|exists:languages,id',
                'actors'              => 'required|exists:actors,id',
                'videos.*.url'        => 'required|url',
                'videos.*.tube_id'    => 'required|exists:tubes,id',
                'videos.*.created_at' => 'date',
                'series'              => 'required|exists:series,id'
            ]
        );

        $episode = Series::find($request->input('series'))->episodes()->create(
            [
                'name'        => $request->input('name'),
                'language_id' => $request->input('language')
            ]
        );


        $episode->actors()->sync($request->input('actors'));

        $episode->videos()->createMany($request->input('videos'));

        return response()->json([ 'message' => 'Successfuly Created' ], JsonResponse::HTTP_CREATED);
    }


    /**
     * @param \App\Models\Episode $episode
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Episode $episode): JsonResponse
    {

        return response()->json($episode->load([ 'videos:id,episode_id,url,tube_id,created_at', 'language:id,name', 'actors:id,name', 'series:id,name' ]), JsonResponse::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Episode $episode
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Episode $episode): JsonResponse
    {
        $request->validate(
            [
                'name'                => 'required',
                'language'            => 'required|exists:languages,id',
                'actors'              => 'required|exists:actors,id',
                'videos.*.url'        => 'required|url',
                'videos.*.tube_id'    => 'required|exists:tubes,id',
                'videos.*.created_at' => 'date',
                'series'              => 'required|exists:series,id'
            ]
        );

        $episode->name = $request->input('name');
        $episode->language_id = $request->input('language');
        $episode->actors()->sync($request->input('actors'));

        $episode->videos()->whereNotIn(
            'url',
            array_map(function ($video) {
                return $video['url'];
            }, $request->input('videos'))
        )->delete();

        $episode->videos()->upsert(
            array_map(function ($video) use ($episode) {
                $video['episode_id'] = $episode->id;

                return $video;
            }, $request->input('videos')),
            [ 'url', 'episode_id', 'tube_id' ]
        );
        $episode->save();

        return response()->json([ 'message' => 'Successfuly updated' ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Series $series
     * @param \App\Models\Episode $episode
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Series $series, Episode $episode): JsonResponse
    {
        $episode->delete();

        return response()->json([ 'message' => 'Successfuly deleted' ], JsonResponse::HTTP_OK);
    }
}
