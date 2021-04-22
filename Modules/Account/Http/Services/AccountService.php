<?php namespace Modules\Account\Http\Services;

use Carbon\Carbon;
use Modules\Account\Repositories\AccountRepository;
use Modules\Account\Models\Account;

/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class AccountService
{
    protected $account_repository;

    /**
     * PasswordsService constructor.
     *
     * @param AccountRepository $account_repository
     */
    public function __construct(AccountRepository $account_repository)
    {
        $this->account_repository = $account_repository;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->account_repository->get();
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function store($data)
    {
        return $this->account_repository->create($data);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        return $this->account_repository->find($id);
    }

    /**
     * @param $data
     * @param $id
     */
    public function update($data, $id): void
    {
        $this->account_repository->update($data, $id);
    }

    /**
     * @param $id
     */
    public function destroy($id): void
    {
        $this->account_repository->destroy($id);
    }


    /**
     * @param $data
     * @param $account
     *
     */
    public function syncPermissions($data, Account $account): void
    {
        $this->account_repository->syncPermissions($account, $data);
    }

    public function filterAccountData($data) {
        $record = $this->account_repository->getFilteredAccountData($data);

        return $record;
    }

}
