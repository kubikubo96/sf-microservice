<?php

namespace App\Http\Controllers;

use App\Helpers\Publisher;
use App\Helpers\Response;
use App\Helpers\WorkQueue;

class DemoController extends Controller
{
    public function send()
    {
        try {
            $workQueue = new WorkQueue(config('rabbitmq.micro.wk'));

            $data = [
                'user' => 'tiennt171',
                'age' => 18,
                'email' => 'tiennt171@ghtk.co',
                'phone' => '0977189946'
            ];

            $workQueue->call(json_encode($data));

            return Response::data();
        } catch (\Throwable $e) {
            return Response::dataError($e->getMessage());
        }
    }

    public function sendNotify()
    {
        try {
            $publisher = new Publisher(config('rabbitmq.micro.ps.queue'), config('rabbitmq.micro.ps.exchange'));

            $message = [
                'message' => 'You are my world!'
            ];

            $publisher->call(json_encode($message));

            return Response::data();
        } catch (\Throwable $e) {
            return Response::dataError($e->getMessage());
        }
    }
}
