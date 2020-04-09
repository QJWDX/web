<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class EnableCrossRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if (!is_callable([$response, "header"])) {
            return $response;
        }
        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
        $schema = $request->getScheme();
        if (config('app.env') == "production") {
            $appUrl = config('app.url');
            $host = substr($appUrl, strpos($appUrl, '://') + 3);
            $host = $schema . "://" . $host;
            $allow_origin = [
                $host,//允许访问
            ];
            $response->header('Access-Control-Allow-Origin', $allow_origin);
        } else {
            $response->header('Access-Control-Allow-Origin', "*");
        }
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN, encryptKey');
        $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE');
        $response->header('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
