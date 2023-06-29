<?php

use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;


Route::get('micro/send', [DemoController::class, 'send']);
Route::get('micro/sendNotify', [DemoController::class, 'sendNotify']);
