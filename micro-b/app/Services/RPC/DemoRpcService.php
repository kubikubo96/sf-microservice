<?php

namespace App\Services\RPC;

class DemoRpcService
{
    public function findUserById($request)
    {
        return \App\Helpers\Response::data(['id' => $request['id'], 'username' => 'tiennt171', 'age' => 28]);
    }
}
