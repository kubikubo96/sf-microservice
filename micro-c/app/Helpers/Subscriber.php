<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Publisher Class helper
 */
class Subscriber
{
    private $connection;
    private $queue;
    private $exchange;

    public function __construct($queue, $exchange)
    {
        $this->queue = $queue;
        $this->exchange = $exchange;

        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password')
        );
    }

    /**
     * Publisher call
     * @param  [json] $request
     * @return mixed
     * @throws \Exception
     */
    public function call($callback)
    {
        $channel = $this->connection->channel();

        $channel->queue_declare($this->queue, false, true, false, false);

        $channel->exchange_declare($this->exchange, AMQPExchangeType::DIRECT, false, true, false);

        $channel->queue_bind($this->queue, $this->exchange);

        $channel->basic_consume($this->queue, '', false, true, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
