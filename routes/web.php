<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header('Access-Control-Allow-Headers:x-requested-with,content-type');
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
// $router->get('/wechat', 'WeChatController@serve');
$router->addRoute(['GET', 'POST'], '/wechat', 'WeChatController@serve');
$router->group(['prefix' => 'wechat'], function () use ($router) {
    $router->post('create', 'WeChatController@create');
    $router->get('callback', 'WeChatController@callback');
    $router->get('oauth', 'WeChatController@oauth');


});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->group(['prefix' => 'category'], function () use ($router) {
        $router->post('list', 'CategoryController@list');
    });

    $router->group(['prefix' => 'product'], function () use ($router) {
        $router->post('list', 'ProductController@list');
    });

    $router->group(['prefix' => 'banner'], function () use ($router) {
        $router->post('list', 'BannerController@list');
    });

    $router->group(['prefix' => 'cart'], function () use ($router) {
        $router->post('list', 'CartController@list'); // 购物车列表
        $router->post('add', 'CartController@add');
        $router->post('delete', 'CartController@delete');
    });

    $router->group(['prefix' => 'order'], function () use ($router) {
        $router->post('list', 'OrderController@list');
        $router->post('add', 'OrderController@add');
        $router->post('delete', 'OrderController@delete');
    });

    $router->group(['prefix' => 'address'], function () use ($router) {
        $router->post('list', 'AddressController@list');
        $router->post('add', 'AddressController@add');
        $router->post('delete', 'AddressController@delete');
    });

    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->group(['prefix' => 'order'], function () use ($router) {
            $router->post('show', 'OrderController@show');
        });
        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->post('show', 'UserController@show');
        });
        $router->group(['prefix' => 'category'], function () use ($router) {
            $router->post('list', 'CategoryController@list');
            $router->post('add', 'CategoryController@add');
            $router->post('update', 'CategoryController@update');

        });
        $router->group(['prefix' => 'banner'], function () use ($router) {
            $router->post('list', 'BannerController@list');
            $router->post('add', 'BannerController@add');
            $router->post('update', 'BannerController@update');

        });

        $router->group(['prefix' => 'product'], function () use ($router) {
            $router->post('list', 'ProductController@list');
            $router->post('add', 'ProductController@add');
            $router->post('update_stock', 'ProductController@update_stock');
            $router->post('update', 'ProductController@update');
            $router->post('modify_price', 'ProductController@modifyPrice');
            $router->post('active', 'ProductController@active');




        });

        $router->group(['prefix' => 'image'], function () use ($router) {
            $router->post('upload', 'ImageController@upload');

        });

    });

});

