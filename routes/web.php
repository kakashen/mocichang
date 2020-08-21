<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix' => 'category'], function () use ($router) {
    $router->post('list', 'CategoryController@list');
    $router->post('add', 'CategoryController@add');
    $router->post('update', 'CategoryController@update');

});

$router->group(['prefix' => 'product'], function () use ($router) {
    $router->post('list', 'ProductController@list');
    $router->post('add', 'ProductController@add');
    $router->post('update_stock', 'ProductController@update_stock');

});

$router->group(['prefix' => 'banner'], function () use ($router) {
    $router->post('list', 'BannerController@list');
    $router->post('add', 'BannerController@add');
    $router->post('update', 'BannerController@update');

});

$router->group(['prefix' => 'cart'], function () use ($router) {
    $router->post('list', 'CartController@list'); // 购物车列表
    $router->post('add', 'CartController@add');
    // $router->post('update', 'CategoryController@update');
    $router->post('delete', 'CartController@delete');


});

$router->group(['prefix' => 'order'], function () use ($router) {
    $router->post('list', 'OrderController@list');
    $router->post('add', 'OrderController@add');
    // $router->post('update', 'OrderController@update');
    $router->post('delete', 'OrderController@delete');


});

