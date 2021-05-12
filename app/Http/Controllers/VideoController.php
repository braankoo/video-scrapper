<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FilterTrait;
use App\Jobs\GetViews;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends Controller {

    use FilterTrait;


    /**
     * @param \App\Models\Video $video
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Video $video): JsonResponse
    {
        $video->delete();

        return response()->json([ 'message' => 'Successfuly Deleted' ], JsonResponse::HTTP_OK);
    }


    /**
     * @param \App\Models\Video $video
     */
    public function fetch(Video $video)
    {
        GetViews::dispatch($video);
    }
}
