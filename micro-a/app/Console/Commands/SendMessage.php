<?php

namespace App\Console\Commands;

use App\Helpers\Publisher;
use App\Helpers\Response;
use App\Helpers\WorkQueue;
use Illuminate\Console\Command;

class SendMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work-queue:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Work queue send message';

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
        //$this->send();
        $this->sendNotify();
    }

    public function send()
    {
        $workQueue = new WorkQueue(config('rabbitmq.micro.wk'));

        $data = [
            'user' => 'tiennt171',
            'age' => 18,
            'email' => 'tiennt171@ghtk.co',
            'phone' => '0977189946'
        ];

        $workQueue->producer(json_encode($data));
    }

    public function sendNotify()
    {
        $publisher = new Publisher(config('rabbitmq.micro.ps.queue'), config('rabbitmq.micro.ps.exchange'));

        $message = [
            'message' => 'You are my world!'
        ];

        $publisher->call(json_encode($message));
    }
}
