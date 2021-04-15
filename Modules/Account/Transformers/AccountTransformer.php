<?php namespace Modules\Account\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Account\Models\Account;
use Spatie\Permission\Models\Role;
use Modules\User\Models\UsersPoint;
use Modules\Nomination\Models\CampaignSettings;
use Modules\User\Models\ProgramUsers;
use DB;
use Helper;


class AccountTransformer extends TransformerAbstract
{
    /**
     * @param Account $account
     *
     * @return array
     */
    public function transform(Account $account): array
    {

        date_default_timezone_set('Asia/Dubai');
        $dubai_time = date('Y-m-d H:i:s');
        #update_table_last_login_date_time
        Account::where('id',$account->id)->update(['last_login'=>$dubai_time]);
        
        $userCountry = ProgramUsers::select('country','country_id')->where('account_id',$account->id)->get()->toArray();

        if($account->last_login != null || $account->last_login != ''){
            $last_login = date('M d, Y g:i a', strtotime($account->last_login));
        }else{
            $last_login = null;
        }
        $accountID = Helper::customCrypt($account->id);
        $userID = Helper::customCrypt(optional($account->user)->id);
        return [
            'id'              => $accountID,
            'name'            => ucfirst($account->user->first_name).' '.ucfirst($account->user->last_name),
            'email'           => $account->email,
            'type'            => $account->type,
            'user_id'         => $userID,
            'title'           => optional($account->user)->title,
            'program_id'      => $account->client_admins->client->programs->id ?? $account->user->program_id, //TODO WTF REALLY
            'last_login'      => $last_login,
            'job_title'       => optional($account->user)->job_title,
            'login_ip'        => $account->login_ip,
            'contact_number'  => $account->contact_number,
            'current_balance' => UsersPoint::where('user_id', optional($account->user)->id)->sum('value'),
            'permissions'     => $account->permissions()->pluck('value', 'id'),
            'roles'           => $account->getRoleNames(),//->pluck('name', 'id'),
            'badges'          => $account->badges()->pluck('name', 'active_url'),
            'user_type'       => ($account->user->id == $account->user->vp_emp_number)?'lead':'employee',
            'lead_permissions'=> DB::table('model_has_roles')->select('roles.*')->where(['model_id' => $account->id])->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->get()->first(),
            'group_roles'     => DB::table('users_group_list')->where('account_id', $account->id)->get(),
            'CampaignSettings'=> $account->campaign($account->id),
            'user_country'    => $userCountry[0]['country'],
            'user_country_id'    => $userCountry[0]['country_id']
            

        ];
    }
}


