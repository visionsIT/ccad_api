<?php namespace Modules\Program\Presenters;

use Laracodes\Presenter\Presenter;
use Modules\User\Enums\Transaction;

class ProgramsPointPresenter extends Presenter
{

    /**
     * @return string
     */
    public function createdBy(): string
    {
        return $this->account->name ?? '';
    }

    /**
     * @return mixed
     */
    public function transactionType()
    {
        return Transaction::types()[$this->transaction_type_id];
    }

}
