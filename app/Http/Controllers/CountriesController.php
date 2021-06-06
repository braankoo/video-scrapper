<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountriesController extends Controller {


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->has('search'))
        {
            return response()->json(Country::whereRaw('name LIKE ?', [ '%' . $request->input('search') . '%' ])->simplePaginate(10, [ '*' ]));
        }

        return response()->json(Country::paginate(10, [ 'id', 'name' ], 'page', $request->input('page')), 200);
    }
}
