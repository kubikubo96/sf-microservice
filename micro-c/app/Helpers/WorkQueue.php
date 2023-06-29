<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Work Queue Class helper
 */
class WorkQueue
{
    private $queue;
    private $connection;
    private $channel;

    public function __construct($queue)
    {
        $this->queue = $queue;
        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password'),
            config('rabbitmq.connection.vhost')
        );
        $this->channel = $this->connection->channel();
    }

    /**
     * Work queue producer
     */
    public function producer($request)
    {
        $this->channel->queue_declare($this->queue, false, true, false, false);

        $message = new AMQPMessage($request);
        $this->channel->basic_publish($message, '', $this->queue);

        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Work queue consumer
     */
    public function consumer($callback)
    {
        $this->channel->basic_consume($this->queue, '', false, true, false, false, $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }
}
