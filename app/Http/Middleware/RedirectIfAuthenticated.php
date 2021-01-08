<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        die('welcome');
        if ($this->auth->guest())
        {
            if ($request->ajax())
            {
                return response('Unauthorized.', 401); // Or, return a response that causes client side js to redirect to '/routesPrefix/myIdp1/login'
            }
            else
            {
                $saml2Auth = new Saml2Auth(Saml2Auth::loadOneLoginAuthFromIpdConfig('ccad'));
                return $saml2Auth->login(URL::full());
            }
        }

        return $next($request);
    }
}
