<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Work Queue Class helper
 */
class ListenQueue
{
    private $queue;
    private $connection;
    private $callback;

    public function __construct($queue, $callback)
    {
        $this->queue = $queue;
        $this->callback = $callback;

        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password'),
            config('rabbitmq.connection.vhost')
        );
    }

    /**
     * Work queue call
     *
     * @throws \Exception
     */
    public function call()
    {
        $channel = $this->connection->channel();
        $channel->basic_consume($this->queue, '', false, true, false, false, $this->callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
