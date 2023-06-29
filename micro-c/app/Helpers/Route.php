<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * Route Class helper
 */
class Route
{
    protected $route;
    public function __construct($route)
    {
        $this->route = $route;
    }

    public function response($body)
    {
        $action = '';
        $parameters = [$body];

        $header = Arr::get($body, 'headerParam', []);
        $GLOBALS['header'] = $header;
        $token = Arr::get($header, 'authorization', '');

        // get request method
        $request_method = strtolower(Arr::get($body, 'requestMethod', ''));
        if (!$this->allowRequestMethod($request_method)) {
            return Response::dataError('Method not allow');
        }

        if (strtolower($this->route['method']) === 'resource') {
            $controller = 'App\Http\Controllers\\' . $this->route['action'];

            // Method GET
            if ($request_method === 'get') {
                $id = Arr::get($body, 'pathParam', '');
                if ($id) {
                    // get by id
                    $action = 'show';
                    $parameters = [$id];
                } else {
                    // get list
                    $action = 'list';
                    $url_param = Arr::get($body, 'urlParam', []);
                    $request = $url_param ? Request::extracUrlParam($url_param) : [];
                    $request['token'] = $token;
                    $parameters = [$request];
                }
            }
            // Method POST
            if ($request_method === 'post') {
                $action = 'store';
                $request = Arr::get($body, 'bodyParam', []);
                $request['token'] = $token;
                $parameters = [$request];
            }
            // Method PUT
            if ($request_method === 'put') {
                $action = 'update';
                $id = Arr::get($body, 'pathParam', '');
                $request = Arr::get($body, 'bodyParam', []);
                $request['token'] = $token;

                if (!$id) {
                    return Response::dataError('Method not allow');
                }
                $parameters = [$id, $request];
            }
            // Method DELETE
            if ($request_method === 'delete') {
                $action = 'destroy';
                $id = Arr::get($body, 'pathParam', '');
                if (!$id) {
                    return Response::dataError('Method not allow');
                }
                $parameters = [$id];
            }
        } else {
            if ($request_method !== strtolower($this->route['method'])) {
                return Response::dataError('Method not allow');
            }

            $route_arr = explode('@', $this->route['action']);

            if (count($route_arr) !== 2) {
                return Response::dataError('API Not Found', 404);
            }

            $controller = 'App\Http\Controllers\\' . $route_arr[0];
            $action = $route_arr[1];

            if ($request_method === 'get') {
                $url_param = Arr::get($body, 'urlParam', []);
                $request = $url_param ? Request::extracUrlParam($url_param) : [];
                $request['token'] = $token;
                $parameters = [$request];
            } else {
                $request = Arr::get($body, 'bodyParam', []);
                $request['token'] = $token;
                $id = Arr::get($body, 'pathParam', '');
                if ($id) {
                    $parameters = [$request, $id];
                } else {
                    $parameters = [$request];
                }
            }
        }

        if (!$controller || !$action) {
            return Response::dataError('API Not Found', 404);
        }

        if (!class_exists($controller)) {
            return Response::dataError('Class ' . $controller . ' Not Found', 500);
        }

        if (!method_exists($controller, $action)) {
            return Response::dataError('Action ' . $action . ' Not Found', 500);
        }


        try {
            return call_user_func_array([new $controller, $action], $parameters);
        } catch (\Throwable $e) {
            Log::info('Server Error ' . $e->getMessage());
            return config('app.env') === 'production' ? Response::dataError('Server Error.', 500) : Response::dataError($e->getMessage(), 500);
        }
    }

    private function allowRequestMethod($request_method)
    {
        return in_array(strtolower($request_method), [
            'get',
            'post',
            'put',
            'patch',
            'delete'
        ]);
    }
}
