<?php

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$router->group(['prefix' => 'settings' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'settings.index', 'uses' => 'SettingController@index']);
    $router->post('/', ['as' => 'settings.store', 'uses' => 'SettingController@store']);
    $router->get('/{id}', ['as' => 'settings.show', 'uses' => 'SettingController@show']);
    $router->put('/{id}', ['as' => 'settings.update', 'uses' => 'SettingController@update']);
    $router->delete('/{id}', ['as' => 'settings.delete', 'uses' => 'SettingController@destroy']);
    $router->post('/update/image' , ['as' => 'settings.updateImage' , 'uses' => 'SettingController@updateImageSettings']);
});

$router->group(['prefix' => 'faqs' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'faqs.index', 'uses' => 'FaqController@index']);
    $router->post('/', ['as' => 'faqs.store', 'uses' => 'FaqController@store']);
    $router->get('/{id}', ['as' => 'faqs.show', 'uses' => 'FaqController@show']);
    $router->put('/{id}', ['as' => 'faqs.update', 'uses' => 'FaqController@update']);
    $router->delete('/{id}', ['as' => 'faqs.delete', 'uses' => 'FaqController@destroy']);
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


$router->group(['prefix' => 'is_approved' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->post('/order', ['as' => 'order_delivery.checkOrderDeliveryAdmin', 'uses' => 'OrderDeliveryController@checkOrderApproved']);
    $router->post('/order/date' , ['as' => 'order_deliveryDate.checkOrderDeliveryAdminByDate', 'uses' => 'OrderDeliveryController@checkOrderApprovedByDate']);
});

$router->group(['prefix' => 'ordersByDate' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/', ['as' => 'orders.ibdexByDate', 'uses' => 'OrderController@indexByData']);
    $router->get('/specific-date', ['as' => 'orders.ibdexByDate', 'uses' => 'OrderController@getOrdersPerDate']);
});

$router->group(['namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->put('/is_active/product/{id}', ['as' => 'order_delivery.checkOrderDeliveryAdmin', 'uses' => 'ProductController@updateProduct']);
    $router->put('/update/withdraw/status/{id}' , ['as' => 'withdraw.updateStatus', 'uses' => 'AccountController@updateWithdrawStatus']);
    $router->put('/update/deposit/status/{id}' , ['as' => 'deposit.updateStatus', 'uses' => 'AccountController@updateDepositStatus']);
    $router->put('/update/order/delivery/status/{id}' , ['as' => 'update.orderDelivery', 'uses' => 'OrderController@updateOrderDeliveryStatus']);
    $router->post('/order/cancel' , ['as' => 'order.cancelSingle', 'uses' => 'OrderDeliveryController@cancelOrderDelivery']);
});

$router->group(['prefix' => 'gifts' ,'namespace' => 'App\Http\Controllers\Admin'], function () use ($router) {
    $router->get('/' , ['as' => 'gifts.ordersIndex' , 'uses' => 'GiftController@index']);
    $router->get('/{id}' , ['as' => 'gifts.ordersShow' , 'uses' => 'GiftController@show']);
});

$router->get('/approve-delete-request' , ['as' => 'admin.get_approve_request', 'uses' => 'App\Http\Controllers\Admin\AccountController@indexApproveDeletionRequest']);
$router->put('/approve-delete-request/{id}' , ['as' => 'admin.approve_delete_request', 'uses' => 'App\Http\Controllers\Admin\AccountController@approveRequestDeletion']);
$router->get('/get-users-deleted' , ['as' => 'admin.users_deleted', 'uses' => 'App\Http\Controllers\Admin\AccountController@getUserTrashed']);
$router->get('/get-contacts' , ['as' => 'admin.contact_us', 'uses' => 'App\Http\Controllers\Admin\AccountController@getContactUsData']);

$router->get('/user/info' , ['as' => 'admin.userInfo', 'uses' => 'App\Http\Controllers\Admin\AccountController@getUserInfo']);
