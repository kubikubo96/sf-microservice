<?php

namespace App\Helpers;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

/**
 * RpcServer Class
 * Communication between the microservice
 */
class RpcServer
{
    private $connection;

    public function __construct(){
        $host = config('rabbitmq.connection.host');
        $port = config('rabbitmq.connection.port');
        $user = config('rabbitmq.connection.user');
        $password = config('rabbitmq.connection.password');
        $vhost = config('rabbitmq.connection.vhost');

        $this->connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
    }

    public function handle($queue, $exchange, $callback){
        $channel = $this->connection->channel();

        $channel->queue_declare($queue, false, true, false, false);

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
