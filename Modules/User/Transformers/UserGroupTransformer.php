<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersPoint;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\UsersGroupList;
use Helper;
class UserGroupTransformer extends TransformerAbstract
{
    /**
     * @param UsersGroupList $User
     *
     * @return array
     */
    public function transform(UsersGroupList $User): array
    {
        $programUserData = $User->programUserData->toArray();
        $toId = Helper::customCrypt($programUserData['id']);
        unset($programUserData['id']);
        $programUserData['id'] = $toId;
        
        $id = $User->uglId;
        if($id == '' || $id == null){
            $id = $User->id;
        }

        return [
            'id' => $id,
            'group_id' => $User->user_group_id,
            'role_id' => $User->user_role_id,
            'account_id' => $User->account_id,
            'name' => ucfirst($User->account->user->first_name).' '.ucfirst($User->account->user->last_name),
            'email' => $User->account->email,
            'status' => $User->status,
            'contact_number' => $User->account->contact_number,
            'type' => $User->account->type,
            'def_dept_id' => $User->account->def_dept_id,
            'last_login' => $User->account->last_login,
            'email_verified_at' => $User->account->email_verified_at,
            'login_ip' => $User->account->login_ip,
            'programUserData' => $User->programUserData,
            'company' => $User->programUserData->company,
            'country' => $User->programUserData->country,
            'country_id' => $User->programUserData->country_id
        ];

    }
}
