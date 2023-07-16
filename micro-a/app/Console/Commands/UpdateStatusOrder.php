<?php

namespace App\Console\Commands;

use App\Helpers\WorkQueue;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStatusOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work-queue:update-status-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Work queue update status order';

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
        (new WorkQueue(config('rabbitmq.micro.wk_update_status')))->consumer(function ($request) {
            try {
                $request = json_decode($request->body, true);
                DB::beginTransaction();
                Order::where('id', $request['id'])->update(['status' => $request['status']]);
                DB::commit();
                $this->info('Update status order success. ' . $request['status']);
                $this->info('Send notify for users');
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error($e->getMessage());
            }
        });
    }

}
