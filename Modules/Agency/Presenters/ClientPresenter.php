<?php namespace Modules\Agency\Presenters;

use Laracodes\Presenter\Presenter;

class ClientPresenter extends Presenter
{

    /**
     * @return string
     */
    public function adminsCount(): string
    {
        return $this->admins->count();
    }

    /**
     * @return string
     */
    public function programsCount(): string
    {
        return $this->programs->count();
    }

}
