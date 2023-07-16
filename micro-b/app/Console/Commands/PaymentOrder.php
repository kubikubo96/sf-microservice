<?php

namespace App\Console\Commands;

use App\Helpers\WorkQueue;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
                $request = json_decode($request->body, true);
                DB::beginTransaction();
                $order = $request['order'];
                Payment::create(['order_id' => $order['id'], 'price' => $order['price'], 'status' => 'success']);

                $workQueue = new WorkQueue(config('rabbitmq.micro.wk_update_status'));
                $workQueue->producer(json_encode(['id' => $order['id'], 'status' => 'paid_success']));

                DB::commit();
                $this->info('Payment order Success');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error($e->getMessage());
            }
        });
    }

}
