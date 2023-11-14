<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$router->group([ 'namespace' => 'App\Http\Controllers\Auth'], function () use ($router) {
    $router->post('{type}/login', ['as' => 'auth.login', 'uses' => 'AuthController@login']);
    $router->post('/register', ['as' => 'auth.register', 'uses' => 'AuthController@register']);
    $router->put('/update-user/{id}', ['as' => 'auth.update', 'uses' => 'AuthController@updateUser'])->middleware('auth:api');
    
    // $router->post('/forget-password', ['as' => 'auth.forget', 'uses' => 'AuthController@forgetPassowrd']);
    $router->post('/forget-password', ['as' => 'auth.forget', 'uses' => 'AuthController@forgetPassowrdMatch']);
    // $router->post('/forget-reset-password', ['as' => 'auth.forget_reset', 'uses' => 'AuthController@resetPassword']);
    $router->post('/forget-reset-password', ['as' => 'auth.forget_reset', 'uses' => 'AuthController@resetPasswordMatch']);

    $router->get('/logout', ['as' => 'auth.logout', 'uses' => 'AuthController@logout'])->middleware('auth:api');
});