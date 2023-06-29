<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Helpers\WorkQueue;

class DemoController extends Controller
{
    public function send()
    {
        try {
            $workQueue = new WorkQueue(config('rabbitmq.micro.queue'));

            $data = [
                'user' => 'tiennt171',
                'age' => 18,
                'email' => 'tiennt171@ghtk.co',
                'phone' => '0977189946'
            ];

            $workQueue->producer(json_encode($data));

            return Response::data();
        } catch (\Throwable $e) {
            return Response::dataError($e->getMessage());
        }
    }

}
