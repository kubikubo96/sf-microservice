<?php

namespace App\Services;

use App\Helpers\RpcClient;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * UserClient Class helper
 */
class UserClient
{

    /**
     * Get user list by uuid list
     * @param array $uuids
     * @return void
     */
    public static function getUsersByListId(array $uuids)
    {
        $queue = config('rabbitmq.user.rpc.queue');
        $rpc_client = new RpcClient($queue);

        $request = [
            'requestMethod' => 'POST',
            'requestPath' => '/v1.0/user/uuidLst',
            'urlParam' => '',
            'pathParam' => '',
            'bodyParam' => [
                'uuids' => $uuids
            ]
        ];

        $data = [];
        $startTime = round(microtime(true) * 1000);
        Log::channel('user_client')->info('Sending get-user-list-by-uuid-list request [' . json_encode($request) . ']');
        try {
            $response = $rpc_client->call(json_encode($request));
            $spentMs = round(microtime(true) * 1000) - $startTime;
            Log::channel('user_client')->info('Received get-user-list-by-uuid-list response [' . json_encode($response) . ']. Spent ' . $spentMs . ' ms');

            $response = json_decode($response, true);
            if (isset($response['data']['status']) && $response['data']['status'] === 200) {
                $data = $response['data']['data'];
            }
        } catch (Exception $e) {
            Log::channel('user_client')->error('Exception: ' . $e->getMessage());
            $data = [];
        }

        return $data;
    }

}
