<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::apiResource('actor', \App\Http\Controllers\ActorController::class);
Route::apiResource('series', \App\Http\Controllers\SeriesController::class);
Route::get('/series/{series}/stats', [ \App\Http\Controllers\SeriesController::class, 'stats' ]);

Route::apiResource('language', \App\Http\Controllers\LanguageController::class);


Route::get('/episode/stats', [ \App\Http\Controllers\EpisodeController::class, 'stats' ]);
Route::apiResource('episode', \App\Http\Controllers\EpisodeController::class);


Route::get("/series/{series}/episode/{episode}", [ \App\Http\Controllers\EpisodeController::class, 'singleStats' ]);

Route::apiResource('series.episode', \App\Http\Controllers\EpisodeController::class)->except([ 'store' ]);

Route::get('/video', [ App\Http\Controllers\VideoController::class, 'index' ]);
Route::post('/video/fetch/{video}', [ App\Http\Controllers\VideoController::class, 'fetch' ]);
Route::delete('/video/{video}', [ App\Http\Controllers\VideoController::class, 'destroy' ]);


Route::get('/stats', [ \App\Http\Controllers\StatsController::class, 'index' ]);
Route::get('/stats/episode', [ \App\Http\Controllers\StatsController::class, 'episode' ]);

Route::post('/stats', [ \App\Http\Controllers\StatsController::class, 'update' ]);
Route::get('/stats/csv', [ \App\Http\Controllers\StatsController::class, 'csv' ]);


Route::get('/check', [ \App\Http\Controllers\LoginController::class, 'check' ]);


Route::get('/countries', [ \App\Http\Controllers\CountriesController::class, 'index' ]);
