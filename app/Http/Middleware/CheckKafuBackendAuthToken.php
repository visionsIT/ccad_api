<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Route;
use \Illuminate\Http\Request;
use Modules\Account\Models\Account;
use Illuminate\Auth\AuthenticationException;
use DB;
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

         $routeName = Route::currentRouteName();

        // echo ($routeName); // loginpassport.token

         //$route = $request->route()->getName();







        return $result;

    }
}
