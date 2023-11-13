<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$router->group([ 'namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->post('/reset-password', ['as' => 'user.reset', 'uses' => 'UserController@resetPassword']);
});

$router->group(['prefix' => 'products' ,'namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->get('/', ['as' => 'products.index', 'uses' => 'ProductController@index']);
});