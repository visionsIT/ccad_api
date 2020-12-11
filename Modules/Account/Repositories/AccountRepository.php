<?php namespace Modules\Account\Http\Repositories;

use App\Repositories\Repository;
use Modules\Account\Models\Account;

class AccountRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = Account::class;

    /**
     * @param $email
     *
     * @return mixed
     */
    public function findAccountByEmail($email)
    {
        return $this->modeler->where('email', $email)->firstOrFail();
    }

    /**
     * @param Account $account
     * @param $data
     * @return mixed
     */
    public function syncPermissions(Account $account, $data)
    {
        return $account->syncPermissions($data['permission_names']);
    }


}
