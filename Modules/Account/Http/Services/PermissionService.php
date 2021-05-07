<?php namespace Modules\Account\Http\Services;

use Modules\Account\Models\Account;
use Modules\Account\Repositories\PermissionRepository;
use Modules\Account\Models\Permission;

/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class PermissionService
{
    protected $repository;

    /**
     * PasswordsService constructor.
     *
     * @param PermissionRepository $repository
     */
    public function __construct(PermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPermissionUsers($id)
    {
        $permission = $this->repository->find($id);

        $accounts = Account::whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission->name);
        })->get();

        return $accounts->map(function ($account){
            return $account->user;
        })->filter();
    }

    public function getClaimAwardPermission() {
        // $claimAward = Permission::where('name', 'approve_claims')->get()->first();
        $claimAward = Permission::whereIn('name', ['approve_claims', 'birthday_campaign_global', 'e_cards', 'sso_login'])->get();
        return $claimAward;
    }

    public function updateClaimAwardDisplayPermission($request) {
        $claimAward = Permission::where('id', $request['id'])->update(['status' => $request['status']]);
        return $claimAward;
    }

    public function checkBirthdayPermission($data) {
        $birthdayPermission = Permission::where('name', $data['permission_param'])->first();

        return $birthdayPermission;
    }


}
