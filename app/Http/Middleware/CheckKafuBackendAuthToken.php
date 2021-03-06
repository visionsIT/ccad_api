<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;
use \Illuminate\Http\Request;
use Modules\Account\Models\Account;
use Illuminate\Auth\AuthenticationException;

use Auth;



use Closure;

class CheckKafuBackendAuthToken
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
		$result =  $next($request);

		$referer =  $request->header('referer');
		$result->header('Cache-Control', 'no-store, no-cache, must-revalidate');
		
		$routeName = Route::currentRouteName();

		return $result;

    }
}
