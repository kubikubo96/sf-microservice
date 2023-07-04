<?php

namespace App\Console\Commands;

use App\Helpers\Response;
use App\Helpers\Route;
use App\Helpers\RpcServer;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ReplyRpc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reply:rpc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reply rpc';

    private $queue;
    private $exchange;
    protected $rpc_server;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->queue = config('rabbitmq.micro.rpc.queue');
        $this->exchange = config('rabbitmq.micro.rpc.exchange');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /*$connection = new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password'),
            config('rabbitmq.connection.vhost')
        );
        $channel = $connection->channel();

        $channel->queue_declare('rpc_queue', false, false, false, false);

        function fib($n)
        {
            if ($n == 0) {
                return 0;
            }
            if ($n == 1) {
                return 1;
            }
            return fib($n - 1) + fib($n - 2);
        }

        echo " [x] Awaiting RPC requests\n";
        $callback = function ($req) {
            $n = intval($req->body);
            echo ' [.] fib(', $n, ")\n";

            $msg = new AMQPMessage(
                (string)fib($n),
                array('correlation_id' => $req->get('correlation_id'))
            );

            $req->delivery_info['channel']->basic_publish(
                $msg,
                '',
                $req->get('reply_to')
            );
            $req->ack();
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('rpc_queue', '', false, false, false, false, [new ReplyRpc(), 'logData']);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();*/

        function fib($n)
        {
            if ($n == 0) {
                return 0;
            }
            if ($n == 1) {
                return 1;
            }
            return fib($n - 1) + fib($n - 2);
        }

        echo " [x] Awaiting RPC requests\n";
        $callback = function ($req) {
            $n = intval($req->body);
            echo ' [.] fib(', $n, ")\n";

            $msg = new AMQPMessage(
                (string)fib($n),
                array('correlation_id' => $req->get('correlation_id'))
            );

            $req->delivery_info['channel']->basic_publish(
                $msg,
                '',
                $req->get('reply_to')
            );
            $req->ack();
        };

        $rpcServer = new RpcServer();
        $rpcServer->handle('rpc_queue', $this->exchange, $callback);
    }

    public function logData($request)
    {
        $response = ['tiennt171'];
        $number = $request->body;

        $this->publish($request, $response);
    }

    function fib($n)
    {
        if ($n == 0) {
            return 0;
        }
        if ($n == 1) {
            return 1;
        }
        return fib($n - 1) + fib($n - 2);
    }

    private function publish($request, $response)
    {
        $body = json_encode($response);

        /*$message = new AMQPMessage($body, [
            'content_type' => 'text/plain',
            'correlation_id' => $request->get('correlation_id')
        ]);*/
        $message = new AMQPMessage(
            0,
            array('correlation_id' => $request->get('correlation_id'))
        );

        $request->delivery_info['channel']->basic_publish(
            $message,
            '',
            $request->get('reply_to')
        );
        $request->ack();
    }

    public function processMessage($request)
    {
        $body = json_decode($request->body, true);

        $this->publish($request, $body);
    }

    private function log($request, $response)
    {
        logger()->info('app.requests', [
            'request' => $request,
            'response' => $response
        ]);
    }
}
