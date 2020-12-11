<?php namespace Modules\Agency\Observers;

use Modules\Account\Models\Account;
use Modules\Agency\Models\ClientsAdmin;

class ClientAdminObserver
{
    /**
     * Handle the ClientAdmin "created" event.
     *
     * @param  ClientsAdmin $clientAdmin
     *
     * @return void
     */
    public function created(ClientsAdmin $clientAdmin)
    {
        //
    }

    /**
     * @param ClientsAdmin $clientAdmin
     */
    public function creating(ClientsAdmin $clientAdmin)
    {
        //todo search if the creating method take another argument
        $account = Account::create([
            'name'     => $clientAdmin->name,
            'email'    => $clientAdmin->email,
            'password' => bcrypt($clientAdmin->name . '__'),
        ]);

        $clientAdmin->account_id = $account->id;
    }

    /**
     * Handle the ClientAdmin "updated" event.
     *
     * @param  ClientsAdmin $clientAdmin
     *
     * @return void
     */
    public function updated(ClientsAdmin $clientAdmin)
    {
        //
    }

    /**
     * Handle the ClientAdmin "deleted" event.
     *
     * @param  ClientsAdmin $clientAdmin
     *
     * @return void
     */
    public function deleted(ClientsAdmin $clientAdmin)
    {
        //
    }

    /**
     * Handle the ClientAdmin "restored" event.
     *
     * @param  ClientsAdmin $clientAdmin
     *
     * @return void
     */
    public function restored(ClientsAdmin $clientAdmin)
    {
        //
    }

    /**
     * Handle the ClientAdmin "force deleted" event.
     *
     * @param  ClientsAdmin $clientAdmin
     *
     * @return void
     */
    public function forceDeleted(ClientsAdmin $clientAdmin)
    {
        //
    }
}
