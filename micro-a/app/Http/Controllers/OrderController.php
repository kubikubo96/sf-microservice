<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Helpers\RpcClient;
use App\Helpers\WorkQueue;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Choreography-based saga. Tạo order. dựa vào status để xác định các service khác success.
     *
     * @param Request $request
     * @return array
     */
    public function createOrderV1(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name' => 'Lamborghini',
                'price' => 1000,
                'status' => 'unpaid'
            ];

            $order = Order::create($data);
            $workQueue = new WorkQueue(config('rabbitmq.micro.wk_payment_order'));
            $workQueue->producer(json_encode(['order' => $order]));

            DB::commit();
            return Response::data($order);
        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::dataError($e->getMessage());
        }
    }

    /**
     * Choreography-based saga. Tạo order, realtime rpc, transaction từng service
     *
     * @param Request $request
     * @return array
     */
    public function createOrderV2(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name' => 'Lamborghini',
                'price' => 1000,
                'status' => 'success'
            ];
            $order = Order::create($data);

            $rpcClient = new RpcClient(config('rabbitmq.micro.rpc.queue'));
            $request = [
                'requestMethod' => 'POST',
                'requestPath' => '/payment',
                'urlParam' => '',
                'pathParam' => '',
                'headerParam' => [],
                'bodyParam' => ['order' => $order],
            ];

            $response = $rpcClient->call(json_encode($request));
            if (!$response['success']) {
                DB::rollBack();
                return Response::dataError($response['message']);
            }

            DB::commit();
            return Response::data($order);
        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::dataError($e->getMessage());
        }
    }
}
