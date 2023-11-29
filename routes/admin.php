<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$router->group(['prefix' => 'settings' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'settings.index', 'uses' => 'SettingController@index']);
    $router->post('/', ['as' => 'settings.store', 'uses' => 'SettingController@store']);
    $router->get('/{id}', ['as' => 'settings.show', 'uses' => 'SettingController@show']);
    $router->put('/{id}', ['as' => 'settings.update', 'uses' => 'SettingController@update']);
    $router->delete('/{id}', ['as' => 'settings.delete', 'uses' => 'SettingController@destroy']);
});

$router->group(['prefix' => 'products' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'products.index', 'uses' => 'ProductController@index']);
    $router->post('/', ['as' => 'products.store', 'uses' => 'ProductController@store']);
    $router->get('/{id}', ['as' => 'products.show', 'uses' => 'ProductController@show']);
    $router->post('/{id}', ['as' => 'products.update', 'uses' => 'ProductController@update']);
    $router->delete('/{id}', ['as' => 'products.delete', 'uses' => 'ProductController@destroy']);
});

$router->group(['prefix' => 'orders' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'orders.index', 'uses' => 'OrderController@index']);
    $router->post('/', ['as' => 'orders.store', 'uses' => 'OrderController@store']);
    $router->get('/{id}', ['as' => 'orders.show', 'uses' => 'OrderController@show']);
    $router->put('/{id}', ['as' => 'orders.update', 'uses' => 'OrderController@update']);
    $router->delete('/{id}', ['as' => 'orders.delete', 'uses' => 'OrderController@destroy']);
});

$router->group(['prefix' => 'delivery' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'delivery.index', 'uses' => 'DeliveryController@index']);
    $router->post('/', ['as' => 'delivery.store', 'uses' => 'DeliveryController@store']);
    $router->get('/{id}', ['as' => 'delivery.show', 'uses' => 'DeliveryController@show']);
    $router->put('/{id}', ['as' => 'delivery.update', 'uses' => 'DeliveryController@update']);
    $router->delete('/{id}', ['as' => 'delivery.delete', 'uses' => 'DeliveryController@destroy']);
});


$router->group(['prefix' => 'is_approved' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->post('/order', ['as' => 'order_delivery.checkOrderDeliveryAdmin', 'uses' => 'OrderDeliveryController@checkOrderApproved']);
});