<?php

use App\Http\Controllers\DemoController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;


Route::get('micro-a/send', [DemoController::class, 'send']);
Route::get('micro-a/sendNotify', [DemoController::class, 'sendNotify']);

Route::post('micro-a/createOrderV1', [OrderController::class, 'createOrderV1']);
Route::post('micro-a/createOrderV2', [OrderController::class, 'createOrderV2']);
