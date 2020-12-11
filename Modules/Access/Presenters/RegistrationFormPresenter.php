<?php namespace Modules\Access\Presenters;

use Laracodes\Presenter\Presenter;

class RegistrationFormPresenter extends Presenter
{

    /**
     * @return array
     */
    public function formData(): array
    {
        return unserialize($this->form);
    }

}
