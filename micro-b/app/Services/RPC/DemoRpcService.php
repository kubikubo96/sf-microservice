<?php

namespace App\Services\RPC;

class DemoRpcService
{
    public function findUserById($id)
    {
        return \App\Helpers\Response::data(['id' => $id, 'username' => 'tiennt171', 'age' => 28]);
    }
}
