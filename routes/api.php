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


Route::group(['middleware' => ['cors', 'json.response']], function () {

    // public routes
    Route::get('test', 'Auth\ApiAuthController@test'); //used to debug auth issues
    Route::post('/login', 'Auth\ApiAuthController@login')->name('login.api');
    Route::post('/register','Auth\ApiAuthController@register')->name('register.api');
    

    Route::middleware('auth:api')->group(function () {
        // our routes to be protected will go in here

        //customer routes
        Route::get('/customer','Api\CustomerController@index');
        Route::get('customer/{customerId}','Api\CustomerController@show');
        Route::get('customer/{customerId}/update','Api\CustomerController@update');
        Route::get('customer/{customerId}/delete','Api\CustomerController@destroy');
        
        //product routes
        Route::get('product/myProducts','Api\ProductController@myProducts');
        Route::get('product/searchByCategory/{categoryName}','Api\ProductController@searchByCategory');
        Route::get('product/findByName/{productName}', 'Api\ProductController@findByName');
        Route::post('product/buy/{productId}', 'Api\ProductController@buy');
        Route::resource('product','Api\ProductController');

        //cart routes
        Route::get('/cart/emptycart','Api\CartController@emptyCart');
        Route::get('/cart/buycart', 'Api\CartController@buyCart');
        Route::post('/cart/buy/{itemId}', 'Api\CartController@buyCartItem');
        Route::post('cart/{productId}', 'Api\CartController@addToCart');
        Route::resource('cart','Api\CartController');

        //logout if user is already logged in (requires valid token)
        Route::post('/logout', 'Auth\ApiAuthController@logout')->name('logout.api');
    });
});