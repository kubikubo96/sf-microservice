<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckValidTokenUserLog
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $GLOBALS['token'] = $request->bearerToken();
        if ($token) {
            $jwt_valid = is_jwt_valid($token, config('api.jwt.secret'));
            if (!$jwt_valid) {
                $GLOBALS['token'] = '';
            }
        }
        return $next($request);
    }
}
