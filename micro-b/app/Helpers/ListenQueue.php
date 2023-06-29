<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Work Queue Class helper
 */
class ListenQueue
{
    public function __construct(private $queue, private $callback, private $connection = null)
    {
        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password'),
            config('rabbitmq.connection.vhost')
        );
    }

    /**
     * Work queue listen
     *
     */
    public function listen()
    {
        $channel = $this->connection->channel();
        $channel->basic_consume($this->queue, '', false, true, false, false, $this->callback);

        $channel->close();
        $this->connection->close();
    }
}
