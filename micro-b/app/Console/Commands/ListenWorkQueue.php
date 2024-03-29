<?php

namespace App\Console\Commands;

use App\Helpers\WorkQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ListenWorkQueue extends Command
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
        (new WorkQueue(config('rabbitmq.micro.wk')))->consumer(function ($request) {
            try {
                $this->info("Receive message!");
                $this->info($request->body);
            } catch (\Exception $e) {
                Log::error('Error: ' . $e->getMessage());
            }
        });
    }

}
