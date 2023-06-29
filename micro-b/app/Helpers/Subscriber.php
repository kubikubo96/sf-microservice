<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

/**
 * Publisher Class helper
 */
class Subscriber
{
    private $connection;
    private $exchange;

    public function __construct($exchange)
    {
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

        $channel->exchange_declare($this->exchange, AMQPExchangeType::DIRECT, false, true, false);

        [$queueName] = $channel->queue_declare("", false, false, true, false);

        $channel->queue_bind($queueName, $this->exchange);

        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
