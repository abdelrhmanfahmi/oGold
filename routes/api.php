<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/check-user-exists' , [ApiController::class , 'getUserData'])->name('user.data');
Route::get('/catalog/data/{uuid}' , [ApiController::class , 'getCatalogsData'])->name('catalogs.data');
