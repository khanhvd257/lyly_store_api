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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::middleware('auth:api')->group(function () {
    // api product
    Route::put('/product/{id}/status', 'ProductController@changeStatus');
    Route::put('/product/{id}/delete', 'ProductController@changeDelete');
    Route::put('/product/{id}', 'ProductController@update');

    //api  category
    Route::put('/category/{id}/status', 'CategoryController@changeStatus');
    Route::post('logout', 'AuthController@logout');

    // API UPLOAD ANH
    Route::post('upload', 'UploadController@upload');

    //API GIỎ HÀNG
    Route::post('cart', 'CartController@addToCart');
    Route::put('cart', 'CartController@updateCart');
    Route::delete('cart/{id}', 'CartController@removeCart');
    Route::get('cart', 'CartController@getCartByUsername');


    //API ĐẶT HÀNG
    Route::post('order', 'OrderController@orderProduct');
    Route::put('order/{id}/confirmed', 'OrderController@confirmOrder');
    Route::put('order/{id}/cancel', 'OrderController@cancelOrder');
    Route::put('order/{id}/done', 'OrderController@doneOrder');
    Route::get('order/{id}', 'OrderController@getDetailOrder');
//    Route::get('order-user', 'OrderController@getOrderByUser');
    Route::post('cart-order', 'CartController@changeToOrder'); // chuyển đổi từ giỏ hàng sang order
    Route::get('order/{status?}', 'OrderController@getOrderByUsername');

    // API ĐÁNH GIÁ ĐƠN HÀNG SAU KHI HOÀN THÀNH
    Route::post('rating', 'RatingsController@createRating');
    Route::get('rating', 'RatingsController@getRating');

    Route::post('reply-rating', 'ReplyRatingController@replyRating');

    //API XEM DANH SÁCH TẤT CẢ ĐƠN HÀNG PHÍA NGƯỜI BÁN
    Route::get('orders', 'OrderController@getAllOrder');

//    API LAY RA THONG TIN NGUOI DUNG HIỆN TẠI

    Route::get('user/current', 'AuthController@getInfoUser');
    Route::put('customer', 'CustomerController@updateUser');
    Route::apiResource('customer', 'CustomerController');


});
Route::get('statistic', 'StatisticController@statisticYear');
Route::get('statistic/product', 'StatisticController@getDetailProductIn12Month');
Route::get('statistic/revenue', 'StatisticController@getorderDone');
Route::get('statistic/order', 'StatisticController@statistics');
Route::get('statistic/overview', 'StatisticController@getRevenueByMonth');
Route::get('rating-product/{product_id?}', 'RatingsController@getRatingByProduct');

//api account
Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');

Route::apiResource('category', 'CategoryController');
Route::apiResource('product', 'ProductController');
Route::get('product-detail/{id}', 'ProductController@getDetaiProduct');

//Sản phẩm bán chạy

Route::get('products', 'ProductController@getAllProductBuyer');

Route::get('recommend', 'ProductController@getTop5NewProduct');

//api lấy tỉnh
Route::get('provices', 'AddressController@index');
Route::get('districts', 'AddressController@getDistrictByProvince');
Route::get('wards', 'AddressController@getWardsByDistrict');
