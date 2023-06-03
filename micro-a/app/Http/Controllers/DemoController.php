<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Helpers\WorkQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class DemoController extends Controller
{
    public function send()
    {
        try {
            $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
            $channel = $connection->channel();

            $channel->queue_declare('task_queue', false, true, false, false);

            $data = "Hello World!";
            $msg = new AMQPMessage(
                $data,
                array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
            );

            $channel->basic_publish($msg, '', 'task_queue');

            echo ' [x] Sent ', $data, "\n";

            $channel->close();
            $connection->close();
            return Response::data();
        } catch (\Throwable $e) {
            return Response::dataError($e->getMessage());
        }
    }

}
