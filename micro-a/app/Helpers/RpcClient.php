<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RpcClient Class
 * Communication between the microservice
 */
class RpcClient
{
    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;
    public $queue;

    public function __construct($queue)
    {
        $this->queue = $queue;
    }

    private function connect()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                config('rabbitmq.connection.host'),
                config('rabbitmq.connection.port'),
                config('rabbitmq.connection.user'),
                config('rabbitmq.connection.password')
            );
        } catch (\Throwable $e) {
            return false;
        }
        return $this->connection;
    }

    public function onResponse($response)
    {
        if ($response->get('correlation_id') == $this->corr_id) {
            $this->response = $response->body;
        }
    }

    public function call($request)
    {
        if ($this->connect()) {
            $this->channel = $this->connection->channel();
            list($this->callback_queue, ,) = $this->channel->queue_declare(
                "",
                false,
                false,
                true,
                false
            );
            $this->channel->basic_consume(
                $this->callback_queue,
                '',
                false,
                true,
                false,
                false,
                [
                    $this,
                    'onResponse'
                ]
            );
            $this->response = null;
            $this->corr_id = uniqid();

            $message = new AMQPMessage(
                $request,
                [
                    'correlation_id' => $this->corr_id,
                    'reply_to' => $this->callback_queue
                ]
            );
            $this->channel->basic_publish($message, '', $this->queue);

            $timeOut = 2; // set timeout call
            while (!$this->response) {
                try {
                    $this->channel->wait(null, false, $timeOut);
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                    break;
                }
            }

            $this->channel->close();
            $this->connection->close();

            return $this->response;
        }
        return json_encode(Response::dataError('Rabitmq not response'));
    }
}
