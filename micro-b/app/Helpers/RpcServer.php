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
        $this->connection = new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password'),
            config('rabbitmq.connection.vhost')
        );
    }

    public function handle($queue, $exchange, $callback){
        $channel = $this->connection->channel();

        $channel->queue_declare('rpc_queue', false, false, false, false);

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
