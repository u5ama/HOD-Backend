<?php

namespace App\Http\Middleware;

use Closure;
use Log;

class Cors
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
        header("Access-Control-Allow-Origin: *");
        Log::info(" I am in CORS");
        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'=> 'Origin, Content-Type, X-Auth-Token'
        ];
        if($request->getMethod() == "OPTIONS") {
            Log::info(" I am in headers");
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return Response::make('OK', 200, $headers);
        }

        $response = $next($request);
        Log::info(" I am in response");
        Log::info($request);

        foreach($headers as $key => $value)
            $response->header($key, $value);
        return $response;
    }
}
