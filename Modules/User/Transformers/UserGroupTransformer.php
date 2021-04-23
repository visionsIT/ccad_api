<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersPoint;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\UsersGroupList;
use Helper;
use DB;
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

        $all_group_data =  DB::table('users_group_list')->select('users_group_list.user_group_id', 'roles.name','users_group_list.user_role_id','user_roles.name as user_role_name'  )
        ->join('roles', 'roles.id', '=', 'users_group_list.user_group_id')
        ->join('user_roles', 'user_roles.id', '=', 'users_group_list.user_role_id')
        ->where(['users_group_list.account_id' => $User->account_id,'users_group_list.user_group_id'=>$User->user_group_id, 'users_group_list.status' => '1'])
        ->get();

        return [
            'id' => $id,
            'group_id' => $User->user_group_id,
            'user_group_all' => $all_group_data ? $all_group_data : '',
            'role_id' => $User->user_role_id,
            'account_id' => $User->account_id,
            'name' => ucfirst($User->account->user->first_name).' '.ucfirst($User->account->user->last_name),
            'email' => $User->account->email,
            'status' => $User->status1,
            'contact_number' => $User->account->contact_number,
            'type' => $User->account->type,
            'def_dept_id' => $User->account->def_dept_id,
            'last_login' => $User->account->last_login,
            'email_verified_at' => $User->account->email_verified_at,
            'login_ip' => $User->account->login_ip,
            'programUserData' => $programUserData,
            'company' => $User->programUserData->company,
            'country' => $User->programUserData->country,
            'country_id' => $User->programUserData->country_id
        ];

    }
}
