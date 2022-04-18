<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */


    public function handle(Request $request, Closure $next)
    {
        $httpOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
        if (in_array($httpOrigin, [
            // 여기 Array에 허용하려는 IP 또는 DOMAIN을 추가하면 된다.
            'http://localhost:3000', // Dev Client Server using CORS
            'http://mankai.shop',
            'https://mankai.shop',
            'http://view.mankai.shop',
            'https://view.mankai.shop',
            'https://api.mankai.shop',
            'http://api.mankai.shop'
            // Prod Client Server using CORS
        ]))
        header("Access-Control-Allow-Origin: ${httpOrigin}");
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers: Accept, Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization,x-xsrf-token,authorization");
        header("Content-type:text/html;charset=utf-8");

        return $next($request);

    }
}
