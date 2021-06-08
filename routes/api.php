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

Route::post('login', 'App\Http\Controllers\Api\UserController@login');
Route::post('register', 'App\Http\Controllers\Api\UserController@register');
Route::get('jabatan', 'App\Http\Controllers\Api\UserController@getJabatan');
Route::get('login', function () {
    return response()->json("Unauthorized", 401);
})->name('login');

Route::group(['middleware' => 'auth:api'], function(){
	Route::get('/logout', 'App\Http\Controllers\Api\UserController@logout');
	Route::post('/user/update', 'App\Http\Controllers\Api\UserController@update');
	Route::get('/user/{id}/getData', 'App\Http\Controllers\Api\UserController@getDataUser');
	Route::get('/user/users', 'App\Http\Controllers\Api\UserController@getDataUsers');
	Route::post('/user/hapus', 'App\Http\Controllers\Api\UserController@hapusUser');
	Route::post('/user/reset-jabatan', 'App\Http\Controllers\Api\UserController@resetJabatan');
	Route::post('/user/reset-password', 'App\Http\Controllers\Api\UserController@resetPassword');
	Route::get('/jabatan/konfirmasi', 'App\Http\Controllers\Api\UserController@getJabatanKonfirmasi');
	Route::get('/jabatan/all', 'App\Http\Controllers\Api\UserController@getJabatanAll');

	Route::get('/bencana/terbaru', 'App\Http\Controllers\Api\BencanaController@bencanaTerbaru');
	Route::get('/bencana', 'App\Http\Controllers\Api\BencanaController@bencana');
	Route::get('/bencana/{id}/detail', 'App\Http\Controllers\Api\BencanaController@bencanaDetail');
	Route::post('/bencana', 'App\Http\Controllers\Api\BencanaController@laporBencana');

	Route::get('/desa', 'App\Http\Controllers\Api\LokasiController@getDesa');
	Route::get('/dusun', 'App\Http\Controllers\Api\LokasiController@getDusun');
	
	Route::get('/notifikasi', 'App\Http\Controllers\Api\NotifikasiController@getNotifikasi');

	Route::get('/status', 'App\Http\Controllers\Api\StatusBencanaController@getStatusBencana');
	Route::post('/status', 'App\Http\Controllers\Api\StatusBencanaController@updateStatusBencana');
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
