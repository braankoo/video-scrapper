<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActorController extends Controller {


    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->has('search'))
        {
            return response()->json(Actor::whereRaw('name LIKE ?', [ '%' . $request->input('search') . '%' ])->simplePaginate(10, [ '*' ]));
        }

        return response()->json(Actor::paginate(10, [ 'id', 'name' ], 'page', $request->input('page')), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([ 'name' => 'required|unique:actors' ]);

        Actor::insert([ 'name' => $request->input('name') ]);

        return response()->json([ 'message' => 'Successfuly created' ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Actor $actor
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Actor $actor): \Illuminate\Http\JsonResponse
    {
        $request->validate([ 'name' => 'required|unique:actors' ]);
        $actor->name = $request->input('name');
        $actor->save();

        return response()->json([ 'message' => 'Successfuly updated' ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Actor $actor
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Actor $actor): JsonResponse
    {
        $actor->delete();

        return response()->json([ 'message' => 'Successfuly deleted' ], JsonResponse::HTTP_OK);
    }
}
