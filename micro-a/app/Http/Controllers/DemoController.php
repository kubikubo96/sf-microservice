<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Helpers\WorkQueue;

class DemoController extends Controller
{
    public function send()
    {
        try {
            $workQueue = new WorkQueue('send-data');
            $workQueue->call("Hello World!");

            return Response::data();
        } catch (\Throwable $e) {
            return Response::dataError($e->getMessage());
        }
    }

}
