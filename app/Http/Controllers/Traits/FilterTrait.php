<?php


namespace App\Http\Controllers\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

trait FilterTrait {


    public function filter(Request $request, Builder|Relation $query): Relation|Builder
    {
        if ($request->has('language_id'))
        {
            $query->whereHas('episode.language', function ($q) use ($request) {
                $q->whereIn('language_id', $request->input('language_id'));
            });
        }

        if ($request->has('actor_id'))
        {
            $query->whereHas('episode.actors', function ($q) use ($request) {
                $q->whereIn('actors_id', $request->input('actor_id'));
            });
        }

        if ($request->has('series_id'))
        {
            $query->whereHas('episode', function ($q) use ($request) {
                $q->whereIn('series_id', $request->input('series_id'));
            });
        }

        return $query;
    }


    /**
     * @param \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param $date
     * @return \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder;
     */
    public function dateRangeFilter(Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query, $date): Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {

        if (!empty($date->start_date))
        {
            $query->whereDate('stats.created_at', '>=', $date->start_date);
        }
        if (!empty($date->end_date))
        {

            $query->whereDate('stats.created_at', '<=', $date->end_date);
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param $language
     * @return \Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder;
     */
    public function languageFilter(\Illuminate\Database\Eloquent\Relations\Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query, $language): Relation|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {
        if (!empty($language))
        {
            $query->whereIn('languages.id', $language);
        }

        return $query;
    }

}
