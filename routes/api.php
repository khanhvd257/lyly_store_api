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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {
    // api product
    Route::apiResource('product', 'ProductController');
    Route::put('/product/{id}/status', 'ProductController@changeStatus');
    Route::put('/product/{id}/delete', 'ProductController@changeDelete');

    //api  category
    Route::apiResource('category', 'CategoryController');
    Route::put('/category/{id}/status', 'CategoryController@changeStatus');
    Route::post('logout','AuthController@logout');
    Route::post('upload', 'UploadController@upload');
    Route::apiResource('user', 'UserController');

});



Route::apiResource('customer', 'CustomerController');

//api account
Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');


//api lấy tỉnh

Route::get('provices', 'AddressController@index');
Route::get('districts', 'AddressController@getDistrictByProvince');
Route::get('wards', 'AddressController@getWardsByDistrict');


