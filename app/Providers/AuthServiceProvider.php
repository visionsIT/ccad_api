<?php namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes(NULL, [ 'prefix' => 'api/oauth' ]);

        Passport::tokensExpireIn(now()->addDays(15));

        Passport::refreshTokensExpireIn(now()->addDays(30));

        Gate::define('user/test', function ($user, $request) {
            // testt gate for 'user/test' API
            return $user->id == 1;
        });


        Gate::define('team_nominations/create', function ($user, $request) {
            
            return $loggedin_user->hasRole('ADPortVP'); // only VPs can nominate

          
        });

        

        
    }
}
