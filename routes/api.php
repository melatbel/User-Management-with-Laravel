<?php

use App\Http\Controllers\Api\UserManagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('usermanagment',UserManagController ::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
