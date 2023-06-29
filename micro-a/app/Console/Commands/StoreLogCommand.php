<?php

namespace App\Console\Commands;

use App\Helpers\Auth;
use App\Helpers\LogHelper;
use App\Library\CGlobal;
use App\Repositories\LogRepository;
use App\Services\TelegramService;
use App\Services\UserClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class StoreLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Work queue store log';

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
        $queue = config('rabbitmq.micro.queue');
        $connection = $this->connection();
        $channel = $connection->channel();
        $channel->basic_consume($queue, '', false, true, false, false, [new StoreLogCommand(), 'storeLog']);

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    /**
     * Connection work queue
     *
     * @return AMQPStreamConnection
     */
    public function connection(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            config('rabbitmq.connection.host'),
            config('rabbitmq.connection.port'),
            config('rabbitmq.connection.user'),
            config('rabbitmq.connection.password'),
            config('rabbitmq.connection.vhost')
        );
    }

    /**
     * Store log from service
     *
     * @param $request
     */
    public static function storeLog($request)
    {
        try {
            $attribute = json_decode($request->body, true);
            Log::info(json_encode($attribute));
        } catch (\Exception $e) {
            TelegramService::sendError($e, $request->body);
            Log::error('[Exception StoreLogCommand - storeLog] ' . $e->getMessage());
        }
    }
}
