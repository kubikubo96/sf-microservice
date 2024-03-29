<?php

namespace App\Console\Commands;

use App\Helpers\WorkQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PaymentOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work-queue:payment-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Work queue payment order';

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
        (new WorkQueue(config('rabbitmq.micro.wk_payment_order')))->consumer(function ($request) {
            try {
                $this->info("Payment Success!");
                Log::info($request->body);
            } catch (\Exception $e) {
                Log::error('Error: ' . $e->getMessage());
            }
        });
    }

}
