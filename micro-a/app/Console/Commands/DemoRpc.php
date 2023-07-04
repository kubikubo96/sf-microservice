<?php

namespace App\Console\Commands;

use App\Helpers\Publisher;
use App\Helpers\Response;
use App\Helpers\RpcClient;
use App\Helpers\WorkQueue;
use Illuminate\Console\Command;

class DemoRpc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:rpc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo RPC';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rpcClient = new RpcClient(config('rabbitmq.micro.rpc.queue'));
        $this->info('DATA: ' . $rpcClient->call(json_encode(['id' => 1])));
    }
}
