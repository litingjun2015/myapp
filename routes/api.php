<?php

use Illuminate\Http\Request;

Use App\Article;


Route::any('wechat', 'WechatController@serve');

// Test
Route::get('articles', 'ArticleController@index');
Route::get('articles/{id}', 'ArticleController@show');
Route::post('articles', 'ArticleController@store');
Route::put('articles/{id}', 'ArticleController@update');
Route::delete('articles/{id}', 'ArticleController@delete');

Route::get('test', 'TestController@index');

//越南语 vi
//Route::post('wechat', 'WechatController@vi');

Route::post('my', 'TestController@my');
