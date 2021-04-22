<?php namespace Modules\Account\Http\Services;

use Modules\Account\Repositories\RoleRepository;
use Modules\Account\Models\Account;

/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class RoleService
{
    protected $repository;

    /**
     * PasswordsService constructor.
     *
     * @param RoleRepository $repository
     */
    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }



    /**
     * @param $id
     * @return mixed
     */
    public function getRoleUsers($id)
    {
        $role = $this->repository->find($id);

        $accounts = Account::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role->name);
        })->get();

        return $accounts->map(function ($account){
            return $account->user;
        })->filter();
    }

    public function assignPermissionsToGroup($id, $permissions)
    {
        $role = $this->repository->find($id);

        $role->syncPermissions($permissions);

        return $role;

    }

    public function getRolePermissions($id)
    {
        $role = $this->repository->find($id);

        return $role->permissions;

    }

}
