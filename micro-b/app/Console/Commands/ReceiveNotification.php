<?php

namespace App\Console\Commands;

use App\Helpers\Subscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReceiveNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'receive:noty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe receive noty';

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
        (new Subscriber(
            config('rabbitmq.micro.ps.queue'), config('rabbitmq.micro.ps.exchange')
        ))->call(function ($request) {
            try {
                $this->info("Receive message!");
                Log::info($request->body);
            } catch (\Exception $e) {
                Log::error('Error: ' . $e->getMessage());
            }
        });
    }

}
