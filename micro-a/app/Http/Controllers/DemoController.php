<?php

namespace App\Http\Controllers;

use App\Helpers\Response;

class DemoController extends Controller
{
    public function send()
    {
        return Response::data();
    }

}
