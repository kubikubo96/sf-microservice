<?php

namespace App\Console\Commands;

use App\Helpers\RpcServer;
use Illuminate\Console\Command;
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
        dump("[x] Awaiting RPC requests");
        $rpcServer = new RpcServer();
        $rpcServer->handle($this->queue, $this->exchange, [new ReplyRpc(), 'logData']);
    }

    public function logData($request)
    {
        $response = ['id' => 1, 'username' => 'tiennt171'];
        dump('$request->body: ' . $request->body);

        $this->publish($request, json_encode($response));
    }

    private function publish($request, $response)
    {
        $message = new AMQPMessage($response, [
            'content_type' => 'application/json',
            'correlation_id' => $request->get('correlation_id')
        ]);

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
}
