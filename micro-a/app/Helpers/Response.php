<?php

namespace App\Helpers;

/**
 * Response Class helper
 */
class Response
{
    public static function data($data = [], $message = 'Successfully', $status = 200)
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function dataError($message = 'Forbidden', $status = 403)
    {
        return self::data([], $message, $status);
    }
}
