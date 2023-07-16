<?php

namespace App\Services\RPC;

use App\Helpers\Response;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentOrder
{
    public function paymentOrder($request)
    {
        try {
            DB::beginTransaction();
            $order = $request['order'];
            $payment = Payment::create(['order_id' => $order['id'], 'price' => $order['price'], 'status' => 'success']);

            DB::commit();
            return Response::dataError($payment);
        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::dataError($e->getMessage());
        }

    }
}
