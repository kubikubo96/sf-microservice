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
    }

    /**
     * Work queue call
     *
     * @param $data
     * @throws \Exception
     */
    public function call($data)
    {
        $channel = $this->connection->channel();
        $channel->queue_declare($this->queue, false, true, false, false);

        $message = new AMQPMessage($data);
        $channel->basic_publish($message, '', $this->queue);

        $channel->close();
        $this->connection->close();
    }
}
