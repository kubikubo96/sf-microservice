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
    private $response;
    private $corrId;
    private $queue;

    public function __construct($queue)
    {
        $this->queue = $queue;
    }

    private function connect()
    {
        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password')
        );
        return $this->connection;
    }

    public function onResponse($response)
    {
        if ($response->get('correlation_id') == $this->corrId) {
            $this->response = $response->body;
        }
    }

    public function call($request)
    {
        if ($this->connect()) {
            $channel = $this->connection->channel();
            list($callbackQueue, ,) = $channel->queue_declare(
                '',
                false,
                false,
                true,
                false
            );
            $channel->basic_consume(
                $callbackQueue,
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
            $this->corrId = uniqid();

            $message = new AMQPMessage(
                $request,
                [
                    'correlation_id' => $this->corrId,
                    'reply_to' => $callbackQueue
                ]
            );
            $channel->basic_publish($message, '', $this->queue);

            $timeOut = 10; // set timeout call
            while (!$this->response) {
                try {
                    $channel->wait(null, false, $timeOut);
                } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                    logger()->error($e);
                    break;
                }
            }

            $channel->close();
            $this->connection->close();

            return $this->response;
        }
        return json_encode(Response::dataError('Rabitmq not response'));
    }
}
