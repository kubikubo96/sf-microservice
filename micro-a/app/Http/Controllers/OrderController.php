<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Helpers\RpcClient;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        try {
            \DB::beginTransaction();
            $data = $request->only(['name', 'price']);
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
                \DB::rollBack();
                return Response::dataError($response['message']);
            }

            \DB::commit();
            return Response::dataError($order);
        } catch (\Throwable $e) {
            \DB::rollBack();
            return Response::dataError($e->getMessage());
        }
    }
}