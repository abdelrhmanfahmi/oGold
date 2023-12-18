<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$router->group(['prefix' => 'is_approved' ,'namespace' => 'App\Http\Controllers\Refinery'], function () use ($router) {
    $router->post('/order', ['as' => 'order_delivery.checkOrderDelivery', 'uses' => 'OrderDeliveryController@checkOrderApproved']);
});

$router->get('/index/orders' , ['as' => 'order_index.refinery', 'uses' => 'App\Http\Controllers\Refinery\OrderDeliveryController@indexOrders']);

$router->group(['prefix' => 'ordersByDate' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'orders.ibdexByDate', 'uses' => 'OrderController@indexByData']);
    $router->get('/specific-date', ['as' => 'orders.ibdexByDate', 'uses' => 'OrderController@getOrdersPerDate']);
});
