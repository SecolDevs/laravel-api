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

Route::post('register', 'Api\AuthController@register');

// Ruta sin autenticacion
Route::get('test', 'Api\AuthController@test');

// Crea rutas con autenticacion
Route::group(
    [
        'middleware' => 'auth:api'
    ],
    function () {
        Route::post('testOauth', 'Api\AuthController@testOauth');
    }
);
// Poner en Rutas Seguras
Route::get('users', 'Api\UserdataController@getUsers');
Route::get('users/{id}', 'Api\UserdataController@getUser');
Route::post('users', 'Api\UserdataController@postUser');
Route::put('users/{id}', 'Api\UserdataController@putUser');
Route::delete('users/{id}', 'Api\UserdataController@deleteUser');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
