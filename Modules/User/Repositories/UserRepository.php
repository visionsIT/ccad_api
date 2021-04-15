<?php namespace Modules\User\Http\Repositories;

use App\Repositories\Repository;
use Modules\Account\Models\Account;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersFeedback;
use Modules\User\Models\UsersPoint;
use Modules\Nomination\Models\CampaignSettings;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use DB;
use App;
use Helper;
//use Mail;

class UserRepository extends Repository
{
    protected $modeler = ProgramUsers::class;

    /**
     * @param $data
     *
     * @return mixed
     */
    public function search($data)
    {

        if( isset($data['campaign_id']) && $data['campaign_id'] != ''){
          

           $get_campaign_setting = CampaignSettings::select('receiver_users','receiver_group_ids')->where('campaign_id', $data['campaign_id'])->first()->toArray();

           if($get_campaign_setting['receiver_users'] == 1){
                $group_ids = $get_campaign_setting['receiver_group_ids'];
                $group_ids = explode(',', $group_ids);
                $accnt_id  = $data['account_id'];
                $keywrd = $data['keyword'];

                $resultdata = ProgramUsers::join('users_group_list as t1', "t1.account_id","=","program_users.account_id")
                 ->where(function ($query) use ($group_ids,$accnt_id) {
                    $query->whereIn('t1.user_group_id', $group_ids);
                    $query->where('t1.status','1');
                    $query->where('t1.user_role_id','1');
                    $query->where('program_users.account_id', '!=',  $accnt_id );
                })->where(function($query) use($keywrd) {

                    $query->where('program_users.first_name', 'like', '%' . $keywrd . '%');
                    $query->orWhere('program_users.last_name', 'like', '%' . $keywrd . '%');
                });

                 $resultCount = $resultdata->get();
                 return $resultCount;
              /* return $pendingHistory;*/





    
               /* return ProgramUsers::join('users_group_list as t1', "t1.account_id","=","program_users.account_id")

                ->where('program_users.first_name', 'like', '%' . $data['keyword'] . '%')
                ->orWhere('program_users.last_name', 'like', '%' . $data['keyword'] . '%')
                ->whereIn('t1.user_group_id', $group_ids)
                ->where('t1.status','1')
                ->where('program_users.account_id', '!=',  $data['account_id'] )
                ->get();
*/
            }
            
      
        }

        return  $this->modeler->where('program_id', $data['program_id'])
           ->where('account_id', '!=',  $data['account_id'] )
           ->where('first_name', 'like', '%' . $data['keyword'] . '%')
           ->orWhere('last_name', 'like', '%' . $data['keyword'] . '%')->get();
    }

    /**
     * @param ProgramUsers $user
     * @param $data
     * @param $program
     *
     * @return bool
     */
    public function createUserAccount(ProgramUsers $user, $data, $program)
    {
        $account = Account::create([
           'name' => $data['username'],
           'email' => $data['email'],
           'password' => bcrypt($data['password']),
           'contact_number' => $data['contact_number'],
           'type' => 'user'
        ]);

        if ($data['role_id']){
            $account->assignRole(Role::findById($data['role_id']));
        }

        return \DB::table('program_users_account')->insert([
           'program_users_id' => $user->id,
           'account_id' => $account->id
        ]);
    }

    public function createNewFeedback($data) {
        try {
            
            if(isset($data['email'])){
                $data['email'] = $data['email'];
            }else{

                $emailData = ProgramUsers::select('email')->where('id', $data['user_id'])->first();
                $data['email'] = $emailData->email;
            }

            if(isset($data['name'])){
                $data['name'] = $data['name'];
            }else{

                $emailData = ProgramUsers::select('first_name','last_name')->where('id', $data['user_id'])->first();
                $data['name'] = $emailData->first_name.' '.$emailData->last_name;
            }

            if(isset($data['phone'])){
                $data['phone'] = $data['phone'];
            }else{

                $emailData = ProgramUsers::select('mobile')->where('id', $data['user_id'])->first();
                $data['phone'] = $emailData->mobile;
            }

            //thank you mail to user
            $email_content["template_type_id"] =  '2';
            $email_content["dynamic_code_value"] = array();
            $email_content["email_to"] = $data["email"];
            $email_data = Helper::emailDynamicCodesReplace($email_content);
            
            //mail send to admin
            $emailcontent["template_type_id"] =  '1';
            $emailcontent["dynamic_code_value"] = array($data['name'],$data['phone'],strip_tags($data['feedback']),$data['email']);
            $emailcontent["email_to"] = env('FEEDBACK_SEND_TO');
            $emaildata = Helper::emailDynamicCodesReplace($emailcontent);


            $feedback_create = UsersFeedback::create([
                'user_id' => isset($data['user_id']) ? $data['user_id'] : NULL,
                'name' => isset($data['name']) ? $data['name'] : NULL,
                'email' => $data['email'],
                'phone' => isset($data['phone']) ? $data['phone']: NULL,
                'feedback' => strip_tags($data['feedback'])
            ]);

            return response()->json(['status' => true, 'message' => 'Thank you for your valuable feedback.' ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage() ]);
        }
    }

    public function fetchUserPoints($id) {
        try {
            $data = UsersPoint::leftJoin('program_users', 'program_users.account_id', '=', 'users_points.created_by_id')->where('users_points.user_id', $id)->get();
            return response()->json(['status' => true, 'data' => $data, 'message' => 'points list of user' ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage() ]);
        }
    }

}
