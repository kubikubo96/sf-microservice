<?php

namespace App\Services;

use GuzzleHttp\Client;

class TelegramService
{
    public static function sendMessage($text)
    {
        $_url = config('telegram.url');
        $_token = config('telegram.token');
        $_chat_id = config('telegram.chat_id');

        if ($_url && $_token && $_chat_id) {
            $uri = $_url . $_token . '/sendMessage?parse_mode=html';
            $params = [
                'chat_id' => $_chat_id,
                'text' => $text,
            ];
            $option['verify'] = false;
            $option['form_params'] = $params;
            $option['http_errors'] = false;
            $client = new Client();
            $response = $client->request("POST", $uri, $option);
            return json_decode($response->getBody(), true);
        }
        return true;
    }

    public static function sendError($exception, string $bodyAMPQ = null)
    {
        $html = '<b>[Error] : </b><code>' . $exception->getMessage() . '</code>' . PHP_EOL;
        $html .= '<b>[File] : </b><code>' . $exception->getFile() . '</code>' . PHP_EOL;
        $html .= '<b>[Line] : </b><code>' . $exception->getLine() . '</code>' . PHP_EOL;
        $html .= '<b>[URL] : </b><a href="' . url()->full() . '">' . url()->full() . '</a>' . PHP_EOL;
        $html .= '<b>[Timestamp] : </b><code>' . now() . '</code>' . PHP_EOL;
        if ($bodyAMPQ) {
            $html .= '<b>[Body AMPQ Message] : </b><code>' . $bodyAMPQ . '</code>' . PHP_EOL;
        }
        self::sendMessage($html);
    }

    public static function errorStoreLogFromService(string $message, string $bodyAMPQ)
    {
        $html = '<b>[Error] : </b><code>' . $message . '</code>' . PHP_EOL;
        $html .= '<b>[Body AMPQ Message] : </b><code>' . $bodyAMPQ . '</code>' . PHP_EOL;
        $html .= '<b>[Timestamp] : </b><code>' . now() . '</code>' . PHP_EOL;
        self::sendMessage($html);
    }
}
