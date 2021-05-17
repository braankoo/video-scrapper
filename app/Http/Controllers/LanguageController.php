<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller {


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->has('search'))
        {
            return response()->json(Language::whereRaw('name LIKE ?', [ '%' . $request->input('search') . '%' ])->simplePaginate(10, [ '*' ]));
        }

        return response()->json(Language::paginate(10, [ 'id', 'name' ], 'page', $request->input('page')), JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([ 'name' => 'required|unique:languages' ]);
        Language::insert([ 'name' => $request->input('name') ]);

        return response()->json([ 'message' => 'Successfuly Created' ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Language $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Language $language): JsonResponse
    {
        $request->validate([ 'name' => 'required|unique:series' ]);
        $language->name = $request->input('name');
        $language->save();

        return response()->json([ 'message' => 'Successfuly updated' ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Language $language
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Language $language): JsonResponse
    {
        $language->delete();

        return response()->json([ 'message' => 'Successfuly deleted' ], JsonResponse::HTTP_OK);
    }
}
