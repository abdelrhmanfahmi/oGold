<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$router->group(['prefix' => 'is_approved' ,'namespace' => 'App\Http\Controllers\Refinery'], function () use ($router) {
    $router->post('/order', ['as' => 'order_delivery.checkOrderDelivery', 'uses' => 'OrderDeliveryController@checkOrderApproved']);
});