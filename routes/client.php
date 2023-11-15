<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$router->group([ 'namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->post('/reset-password', ['as' => 'user.reset', 'uses' => 'UserController@resetPassword']);
});

$router->group(['prefix' => 'products' ,'namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->get('/', ['as' => 'products.index', 'uses' => 'ProductController@index']);
    $router->get('/market-watch', ['as' => 'products.market', 'uses' => 'ProductController@getMarketWatch']);
    $router->post('/buy-gold', ['as' => 'products.buy_gold', 'uses' => 'ProductController@buyGold']);
    $router->post('/sell-gold', ['as' => 'products.sell_gold', 'uses' => 'ProductController@sellGold']);
    $router->get('/get-balance', ['as' => 'products.balance', 'uses' => 'ProductController@getBalance']);
    $router->post('/exchange-gold', ['as' => 'products.exchange_gold', 'uses' => 'ProductController@exchangeGold']);
});