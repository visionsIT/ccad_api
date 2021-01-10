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

         if(isset($_REQUEST['SAMLResponse'])){

             //echo "<pre>"; print_r($_REQUEST); print_r($_COOKIE); die;
            $useremail = ($_REQUEST['email'])?$_REQUEST['email']:'';//'lootahs@clevelandclinicabudhabi.ae';
            $account = Account::where('email', $useremail)->first();

            if(!empty($account)){
                if($account->status == 1){
                    //$roleInfo =  DB::table('model_has_roles')->select('roles.*')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['model_has_roles.model_id' => $account->id])->get()->first();
                    $userInfo = DB::table('program_users')->where('account_id', $account->id)->first();
                    if(empty($userInfo)){
                        header("Location: https://ccad.takreem.ae/login/not-allowed");
                        exit;
                    } else {
                        $successToken =  $account->createToken('userToken'.$account->id)->accessToken;
                        header("Location: https://ccad.takreem.ae/login/".$successToken);
                    }
                } else {
                    header("Location: https://ccad.takreem.ae/login/not-active");
                    exit;
                }
            } else {
               header("Location: https://ccad.takreem.ae/login/not-exist");
            }
            exit();
         } else if ($request->is('api/oauth/token') && $referer == "https://ccadapi.takreem.ae/"  ) {
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
