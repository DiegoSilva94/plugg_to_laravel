<?php

namespace PluggTo\Middleware;

use Closure;
use PluggTo\SDK\PluggTo;
use PluggTo\SDK\PluggToException;
class PluggAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            PluggTo::bootstrap();
        } catch(PluggToException $e) {
            return response('Unauthorized.', 401);
        }
        return $next($request);
    }
}
