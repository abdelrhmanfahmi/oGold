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

$router->group(['prefix' => 'address_books' ,'namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->get('/', ['as' => 'address_books.index', 'uses' => 'AddressBookController@index']);
    $router->post('/', ['as' => 'address_books.store', 'uses' => 'AddressBookController@store']);
    $router->put('/{id}', ['as' => 'address_books.update', 'uses' => 'AddressBookController@update']);
});

$router->group(['prefix' => 'bank-details' ,'namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->get('/', ['as' => 'bank-details.index', 'uses' => 'BankController@index']);
    $router->post('/', ['as' => 'bank-details.store', 'uses' => 'BankController@store']);
    $router->get('/{id}', ['as' => 'bank-details.show', 'uses' => 'BankController@show']);
    $router->put('/{id}', ['as' => 'bank-details.update', 'uses' => 'BankController@update']);
    $router->delete('/{id}', ['as' => 'bank-details.delete', 'uses' => 'BankController@delete']);
});

$router->group(['namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->get('/get-open-positions', ['as' => 'products.open_positions', 'uses' => 'ProductController@getOpenPositions']);
    $router->post('/withdraw', ['as' => 'withdraw.store', 'uses' => 'ProductController@storeWithdraw']);
    $router->post('/deposit', ['as' => 'deposit.store', 'uses' => 'ProductController@storeDeposit']);
    $router->get('/orders/list' , ['as' => 'index.ordersUsers' , 'uses' => 'OrderController@index']);
    $router->put('/cancel-order' , ['as' => 'cancel.order' , 'uses' => 'OrderController@cancelOrder']);
});

$router->group(['prefix' => 'gifts' , 'namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->get('/' , ['as' => 'gift.index', 'uses' => 'GiftController@index']);
    $router->post('/send-gift', ['as' => 'gift.send', 'uses' => 'GiftController@sendGift']);
});

$router->group(['namespace' => 'App\Http\Controllers\Client'], function () use ($router) {
    $router->get('/settings' , ['as' => 'index.settings' , 'uses' => 'SettingController@index']);
});

$router->post('/delete-request' , ['as' => 'user.delete_request', 'uses' => 'App\Http\Controllers\Client\UserController@deleteAccount']);
$router->post('/upload-kyc-file' , ['as' => 'user.kyc', 'uses' => 'App\Http\Controllers\Client\UserController@UploadKYCFile']);
$router->get('/kyc-data' , ['as' => 'index.kyc_data', 'uses' => 'App\Http\Controllers\Client\UserController@getFileUrls']);
