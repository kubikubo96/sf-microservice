<?php

namespace App\Helpers;

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * RpcServer Class
 * Communication between the microservice
 */
class RpcServer
{
    private $connection;

    public function __construct()
    {
        $host = config('rabbitmq.connection.host');
        $port = config('rabbitmq.connection.port');
        $user = config('rabbitmq.connection.user');
        $password = config('rabbitmq.connection.password');
        $vhost = config('rabbitmq.connection.vhost');

        $this->connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
    }

    public function handle($queue, $exchange, $callback)
    {
        $consumer_tag = config('rabbitmq.connection.consumer');

        $channel = $this->connection->channel();

        $channel->queue_declare($queue, false, true, false, false);

        $channel->queue_bind($queue, $exchange);

        $channel->basic_consume($queue, $consumer_tag, false, true, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
