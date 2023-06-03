<?php

namespace App\Http\Controllers;

use App\Helpers\Response;

class DemoController extends Controller
{
    public function receive()
    {
        return Response::data();
    }

}
