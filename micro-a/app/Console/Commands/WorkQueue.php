<?php

namespace App\Console\Commands;

use App\Helpers\Response;
use App\Helpers\Route;
use App\Helpers\RpcServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class WorkQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work-queue:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen queue';

    private $queue;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->queue = config('rabbitmq.micro.wk');
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->info('Listen queue:' . $this->queue);
    }
}
