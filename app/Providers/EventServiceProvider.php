<?php

namespace App\Providers;
//namespace App\Http\Middleware;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Redirect;
use Illuminate\Support\Facades\Route;
use \Illuminate\Http\Request;
use Modules\Account\Models\Account;
use Illuminate\Auth\AuthenticationException;
use Mockery as m;
use DB;
use Auth;
use Closure;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', function ($event) {
            //$messageId = $event->getSaml2Auth()->getLastMessageId();
            // Add your own code preventing reuse of a $messageId to stop replay attacks

            $user = $event->getSaml2User();

            $userData = [
                'id' => $user->getUserId(),
                'attributes' => $user->getAttributes(),
                'assertion' => $user->getRawSamlAssertion()
            ];
            //$username = $userData['attributes']['http://schemas.microsoft.com/identity/claims/displayname'][0];
            $useremail = $userData['attributes']['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress'];

            // $accountArray = [
            //     'id' => $userData['id'],
            //     'username' => $username,
            //     'email' => $useremail,
            //     'assertion' => $userData['assertion']
            // ];

            $account = Account::where('email', $useremail)->first();

            if(!empty($account)){
                if($account->status == 1){
                    //$roleInfo =  DB::table('model_has_roles')->select('roles.*')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['model_has_roles.model_id' => $account->id])->get()->first();
                    $userInfo = DB::table('program_users')->where('account_id', $account->id)->first();
                    if(empty($userInfo)){
                        header("Location: https://ccadapi.takreem.ae/login/not-allowed");
                        exit;
                    } else {
                        $successToken =  $account->createToken('userToken'.$account->id)->accessToken;
                        header("Location: https://ccadapi.takreem.ae/login/".$successToken);
                    }
                } else {
                    header("Location: https://ccadapi.takreem.ae/login/not-active");
                    exit;
                }
            } else {
               header("Location: https://ccadapi.takreem.ae/login/not-exist");
            }
            exit();
        });

        Event::listen('Aacotroneo\Saml2\Events\Saml2LogoutEvent', function ($event) {
           header("Location: https://ccadapi.takreem.ae/login/");
           exit();
            // Auth::logout();
            // Session::save();
        });
    }
}
