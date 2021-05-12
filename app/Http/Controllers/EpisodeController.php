<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FilterTrait;
use App\Models\Episode;
use App\Models\Series;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
    public function indexAll(Request $request): JsonResponse
    {
        $filters = json_decode($request->input('filter'));

        $subQuery = DB::table('actors')->selectRaw('GROUP_CONCAT(name) as actors,episode_id')
            ->join('episode_actor_pivot', 'actors.id', '=', 'episode_actor_pivot.actor_id');
        if (!empty($filters->actors))
        {
            $subQuery->whereIn('actors.id', $filters->actors);
        }
        $subQuery->groupBy('episode_id');

        $query = Episode::select(
            [ 'episodes.name as episodes.name', 'series.id as series_id', 'episodes.id as id', 'series.name as series.name', 'actors', 'languages.name as languages.name', DB::raw('SUM(views) as views') ])
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Series $series, Request $request): JsonResponse
    {
        return response()->json([ $series->episodes()->with([ 'actors', 'language' ])->with([ 'stats' => function ($q) use ($request) {
            $q->select(DB::raw('SUM(views) as views'));
            if ($request->has('start_date') && $request->has('end_date'))
            {
                $q->whereBetween('stats.created_at', [ $request->input('start_date'), $request->input('end_date') ]);
            }
            $q->groupBy('episode_id');

        } ])->paginate('10', [ '*' ], 'page', $request->input('page')) ], JsonResponse::HTTP_OK);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(
            [
                'name'                => 'required|unique:episodes',
                'language'            => 'required|exists:languages,id',
                'actors'              => 'required|exists:actors,id',
                'videos.*.url'        => 'required|url|unique:videos,url',
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
     * @param \App\Models\Series $series
     * @param \App\Models\Episode $episode
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Series $series, Episode $episode, Request $request): JsonResponse
    {
        $filters = json_decode($request->input('filter'));

        $query = $episode->videos()->select(
            [ 'videos.url as url', 'videos.id as id', DB::raw('SUM(views) as views') ])
            ->leftJoin('stats', 'videos.id', '=', 'stats.video_id');

        $query = $this->dateRangeFilter($query, $filters->date);

        return response()->json(
            $query->groupBy('videos.id')
                ->orderBy((!empty($request->input('sortBy')) ? $request->input('sortBy') : 'videos.id'), ($request->input('sortDesc') == 'true' ? 'asc' : 'desc'))
                ->paginate('10', [ '*' ], 'page', $request->input('page'))

            , JsonResponse::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Series $series
     * @param \App\Models\Episode $episode
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Series $series, Episode $episode): JsonResponse
    {
        \Validator::validate($request->all(), [
            'name'        => [ 'unique:episodes,name', Rule::requiredIf(!$request->has('language_id')) ],
            'language_id' => [ 'exists:languages,id', Rule::requiredIf(!$request->has('name')) ]
        ]);


        if ($request->has('name'))
        {
            $episode->name = $request->input('name');
        }
        if ($request->has('language_id'))
        {
            $episode->language_id = $request->input('language_id');
        }

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
