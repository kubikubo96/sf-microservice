<?php

namespace App\Http\Middleware;

use App\Helpers\Auth;
use App\Helpers\Response;
use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if ($token) {
            $user = Auth::user($token);
            if (!empty($user)) {
                return $next($request);
            }
        }
        return Response::error('Forbidden', 403);
    }
}
