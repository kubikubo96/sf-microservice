<?php

use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;


Route::get('work-queue/send', [DemoController::class, 'send']);
