<?php

namespace App\Http\Middleware;

use App\Services\SessionService;
use Closure;
use Illuminate\Support\Facades\Redirect;

class AdminMiddleware
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
        $sessionService = new SessionService();
        $userData = $sessionService->getAdminUserSession();

        // confirm requested user is admin.
        if ( !empty($userData['user_role']) && $userData['user_role'][0]['slug'] == 'admin')
        {
            return $next($request);
        }

        return Redirect::route('admin-login');
    }

}
