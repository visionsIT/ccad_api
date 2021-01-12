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

        $routeName = Route::currentRouteName();

        // echo ($routeName); // loginpassport.token

         $route = $request->route()->getName();

        // echo ($route);
        if ($request->is('api/oauth/token') && $referer == "https://adportbackend.visionssoftware.com/login"  ) {
        //     // Backend call for auth
        //     // need to validate for Backend role
        //     echo (' You are at api/oauth/token ');

             $un = $request->only(['username']);

        //     //echo (" USER NAme is : " . $un['username'] );

             $account = Account::where('email',$un['username']) -> first();




/*                  if (!$account->hasRole('ADPortEngageadmin')){

                 abort(403, 'Access denied');
             } */
         }





        return $result;

    }
}
