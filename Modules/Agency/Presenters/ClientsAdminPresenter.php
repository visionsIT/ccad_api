<?php namespace Modules\Agency\Presenters;

use Laracodes\Presenter\Presenter;

class ClientsAdminPresenter extends Presenter
{

    /**
     * @return string
     */
    public function roleName(): string
    {
        return $this->role === 1 ? 'Global Admin' : 'Program Admin';
    }

}
