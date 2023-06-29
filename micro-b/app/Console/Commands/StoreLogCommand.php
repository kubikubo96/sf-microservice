<?php

namespace App\Console\Commands;

use App\Helpers\ListenQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StoreLogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work-queue:log';

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
        (new ListenQueue(config('rabbitmq.micro.queue'), function ($request) {
            try {
                $attribute = json_decode($request->body, true);
                Log::info(json_encode($attribute));
            } catch (\Exception $e) {
                Log::error('Error: ' . $e->getMessage());
            }
        }))->listen();
    }

}
