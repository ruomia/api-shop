<?php

use Illuminate\Http\Request;

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

Route::get('test',function(){
    return 'fafasdfas';
    // return response('无权访问！', 403);
});
Route::post('members', 'MemberController@store');
Route::post('authorizations','MemberController@login');
Route::get('goods','GoodsController@index');
Route::get('attributes','GoodsController@attribute');
Route::get('skus','GoodsController@getSku');
Route::get('testSn',function(){
    return getOrderSn();
});

Route::middleware(['jwt'])->group(function () {
    Route::get('addresses','AddressController@index');
    // 添加收货人
    Route::post('addresses',"AddressController@store");
    
    Route::get('orders','OrderController@index');
    Route::post('orders', 'OrderController@store');

});
