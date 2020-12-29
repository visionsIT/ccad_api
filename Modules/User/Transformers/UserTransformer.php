<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersPoint;
use Modules\Reward\Models\ProductOrder;
use DB;
class UserTransformer extends TransformerAbstract
{
    /**
     * @param ProgramUsers $User
     *
     * @return array
     */
    public function transform(ProgramUsers $User): array
    {
        $username = $User->first_name.' '.$User->last_name;
        $program_user_id = ProgramUsers::select('id')->where('account_id', $User->account_id)->first();

        if($User->account->last_login != null || $User->account->last_login != ''){
            $last_login = date('M d,Y g:i a', strtotime($User->account->last_login));
        }else{
            $last_login = null;
        }
        
        return [
            'id' => $User->id,
            'name' => $username,
            'email' => filter_var($User->email, FILTER_SANITIZE_EMAIL),
            'username' => $User->username,
            'account_id' => $User->account_id,
            'user_groups' => optional($User->account)->getRoleNames(),
            'user_group_id' => DB::table('users_group_list')->select('user_group_id')->where('account_id', $User->account_id)->where('user_role_id', '1')->get()->user_group_id,
            'address' => $User->address_1 . ' ' . $User->address_2,
            //'user_points' => $User->point_balance,
            'user_points' => UsersPoint::select('balance')->where('user_id', $User->id)->orderBy('id', 'desc')->first(),
            'orders_count' => ProductOrder::where('account_id', $User->account_id)->count(),
            'contact_number' => $User->contact_number,
            'title' => $User->title,
            'first_name' => $User->first_name,
            'last_name' => $User->last_name,
            'company' => $User->company,
            'job_title' => $User->job_title,
            'address_1' => $User->address_1,
            'address_2' => $User->address_2,
            'town' => $User->town,
            'postcode' => $User->postcode,
            'country' => $User->country,
            'telephone' => $User->telephone,
            'mobile' => $User->mobile,
            'date_of_birth' => $User->date_of_birth,
            'communication_preference' => $User->communication_preference,
            'language' => $User->language,
            'status' => $User->is_active,
            'emp_number' => $User->emp_number,
            'vp_emp_number' => $User->vp_emp_number,
            'program_id' => $program_user_id->id,
            'last_login' => $last_login,
        ];
    }
}
