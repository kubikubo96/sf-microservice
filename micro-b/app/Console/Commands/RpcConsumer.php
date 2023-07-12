<?php

namespace App\Console\Commands;

use App\Helpers\Response;
use App\Helpers\Route;
use App\Helpers\RpcServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class RpcConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rpc:consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consumer RPC';

    private $queue;
    private $exchange;

    private $routes;

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
        $this->routes = config('rabbitmq.micro.routes');

    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        dump("*** Awaiting RPC requests");
        $rpcServer = new RpcServer();
        $rpcServer->handle($this->queue, $this->exchange, [new RpcConsumer(), 'processMessage']);
    }

    public function processMessage($request)
    {
        $body = json_decode($request->body, true);
        dump($body);

        if (!isset($body['requestPath']) || !isset($this->routes[$body['requestPath']])) {
            $response = Response::dataError('API Not Found', 404);

            return $this->publish($request, $response);
        }

        $route = new Route($this->routes[$body['requestPath']]);

        $response = $route->response($body);

        $this->log($body, $response);

        return $this->publish($request, $response);
    }

    /**
     * Publish message to rabbitmq
     */
    private function publish($request, $response)
    {
        $body = json_encode($response);

        $message = new AMQPMessage($body, [
            'content_type' => 'application/json',
            'correlation_id' => $request->get('correlation_id')
        ]);

        $request->delivery_info['channel']->basic_publish(
            $message,
            '',
            $request->get('reply_to')
        );
        return $request->ack();
    }

    private function log($request, $response)
    {
        Log::info('app.requests', [
            'request' => $request,
            'response' => $response
        ]);
    }
}
