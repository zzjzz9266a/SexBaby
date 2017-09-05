<?php

use Illuminate\Http\Request;
use App\Baby;
use Illuminate\Support\Facades\DB;

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

Route::post('list', function (Request $request) {
 	return Baby::list($request->get('province'));
});
Route::post('detail/{id}', function ($id, Request $request)
{
	return Baby::detail($id);
});
Route::post('province', function ()
{
	return DB::table('babies')->select('province')->distinct()->get()->map(function ($item)
	{
		return $item->province;
	});
});
Route::post('search', function (Request $request)
{
  $province = $request->get('province');
  $keyword = $request->get('keyword');
  // return $content;
  return Baby::search($province, $keyword);
});
Route::post('collection', function (Request $request)
{
  $ids = $request->get('ids');
  return Baby::collection($ids);
});