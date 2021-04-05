<?php namespace Modules\User\Http\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Account\Models\Account;
use Modules\Program\Models\Program;
use Modules\User\Http\Repositories\UserRepository;
use Modules\User\Http\Requests\ProgramUsersRequest;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersPoint;
use Spatie\Fractal\Fractal;
use Spatie\Permission\Models\Role;
use Modules\User\Models\UsersGroupList;
use Modules\Nomination\Models\CampaignSettings;
use DB;
class UserService
{
    public $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function groupUserList($param = '', $role_id, $group_id){

        if(!empty($param)){
            $search = $param['search'];
            // $userdata = UsersGroupList::with(['account']);
            // if($search != ''){
            //     $userdata = $userdata->whereHas('account',function($q) use ($search){
            //             $q->where( function ($q) use ($search) {
            //             $q->where('name', 'like', '%' . $search . '%')
            //             ->orWhere('email', 'like', '%' . $search . '%');
            //             });
            //         });
            // }

            $userdata = UsersGroupList::select('users_group_list.id as uglId','users_group_list.user_group_id','users_group_list.user_role_id','users_group_list.account_id','users_group_list.status','accounts.*')->where(['users_group_list.user_role_id'=>$role_id,'users_group_list.user_group_id'=>$group_id])->join('accounts','users_group_list.account_id','accounts.id');

            if($role_id == 1){
                $userdata = $userdata->where( function ($q) use ($search) {
                        $q->where('accounts.name', 'like', '%' . $search . '%')
                        ->orWhere('accounts.email', 'like', '%' . $search . '%');
                        })->paginate(20);

            }else{
                $userdata = $userdata->where( function ($q) use ($search) {
                        $q->where('accounts.name', 'like', '%' . $search . '%')
                        ->orWhere('accounts.email', 'like', '%' . $search . '%');
                        })->get()->sortBy('accounts.name',SORT_NATURAL|SORT_FLAG_CASE);
            }

            return $userdata;
        } else {

            if($role_id == 1){
                $userdata = UsersGroupList::select('users_group_list.id as uglId','users_group_list.user_group_id','users_group_list.user_role_id','users_group_list.account_id','users_group_list.status','accounts.*')->join('accounts','users_group_list.account_id','accounts.id')->where(['user_role_id'=>$role_id,'user_group_id'=>$group_id])->paginate(20);
            }else{
                $userdata = UsersGroupList::select('users_group_list.id as uglId','users_group_list.user_group_id','users_group_list.user_role_id','users_group_list.account_id','users_group_list.status','accounts.*')->join('accounts','users_group_list.account_id','accounts.id')->where(['user_role_id'=>$role_id,'user_group_id'=>$group_id])->get()->sortBy('account.name',SORT_NATURAL|SORT_FLAG_CASE);
            }

            return $userdata;
        }
    }

    function sort_by_name($a,$b)
    {
        return $a["account"]["name"] > $b["account"]["name"];
    }

    /**
     * @return Fractal
     */
    public function get($param = '')
    {
        if(!empty($param)){
            $search = $param['search'];
            $column = $param['column'];
            $order = $param['order'];

            $getUserList = ProgramUsers::leftJoin('accounts', 'accounts.id', '=', 'program_users.account_id')->select('program_users.*')->where('program_users.first_name', 'like', '%' . $search . '%');
                if($search != ''){
                    $getUserList = $getUserList->orwhere('program_users.last_name', 'like', '%' . $search . '%')
                    ->orwhere('program_users.email', 'like', '%' . $search . '%')
                    ->orwhere('program_users.job_title', 'like', '%' . $search . '%');
                }

            if($column == 'last_login' || $column == 'login_attempts'){
                $getUserList = $getUserList->orderBy('accounts.'.$column, $order)->paginate(12);
            }else{
                $getUserList = $getUserList->orderBy('program_users.'.$column, $order)->paginate(12);
            }
            
            return $getUserList;
        } else {
            return $this->repository->paginate(12);
        }
    }

    public function getAllUsers($param = '', $campaign_id= '')
    {

        if($campaign_id){

            $useraccount = \Auth::user();
            $accountID =  $useraccount->id;

            $get_campaign_setting = CampaignSettings::select('receiver_users','receiver_group_ids')->where('campaign_id', $campaign_id)->first()->toArray();

            if($get_campaign_setting['receiver_users'] == 1){
                $group_ids = $get_campaign_setting['receiver_group_ids'];
                $group_ids = explode(',', $group_ids);

                return ProgramUsers::join('users_group_list as t1', "t1.account_id","=","program_users.account_id")
                ->select('program_users.*')
                ->whereIn('t1.user_group_id', $group_ids)
                ->where('t1.account_id','!=',$accountID)
                ->where('t1.user_role_id','1')
                ->where('t1.status','1')
                ->get();

            } else {
                return ProgramUsers::join('users_group_list as t1', "t1.account_id","=","program_users.account_id")
                ->select('program_users.*')
                ->where('t1.user_role_id','1')
                ->where('t1.account_id','!=',$accountID)
                ->where('t1.status','1')
                ->get();
            }
        }


        if(!empty($param)){

            $search = $param['search'];
            $column = $param['column'];
            $order = $param['order'];

            $getUserList = ProgramUsers::where('first_name', 'like', '%' . $search . '%');
                if($search != ''){
                    $getUserList = $getUserList->orwhere('last_name', 'like', '%' . $search . '%')
                    ->orwhere('email', 'like', '%' . $search . '%')
                    ->orwhere('job_title', 'like', '%' . $search . '%');
                }
            $getUserList = $getUserList->orderBy($column, $order)->get();

            return $getUserList;
        } else {

            return $this->repository->get();
        }
    }

    private function getCampaignSettings($campaignId) {
        $get_campaign_setting = CampaignSettings::select('receiver_users','receiver_group_ids')->where('campaign_id', $campaignId)->first()->toArray();

        if($get_campaign_setting['receiver_users'] == 1){
            $group_ids = $get_campaign_setting['receiver_group_ids'];
            $group_ids = explode(',', $group_ids);

            return $group_ids;
        } else {
            return array();
        }
    }

    public function getSearchedUsers($request) {
        $search = trim($_REQUEST['keyword']);
        $campaignId = $_REQUEST['campaign_id'];

        if($campaignId) {
            $useraccount = \Auth::user();
            $accountID =  $useraccount->id;

            $group_ids = $this->getCampaignSettings($campaignId);

            $data = ProgramUsers::select('program_users.id', 'program_users.account_id', 'first_name', 'last_name', 'email', 'profile_image', 'image_path')
            ->where('program_users.account_id','!=',$accountID)
            ->where(['program_users.is_active' => 1])
            ->where(function($query) use ($search){
                $query->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'Like', "%{$search}%")
                ->orWhereRaw("concat(program_users.first_name, ' ', program_users.last_name) like '%{$search}%' ");
            })
            ->leftJoin('users_group_list as t1', "t1.account_id","=","program_users.account_id")
            ->where('t1.account_id','!=',$accountID)
            ->where('t1.user_role_id','1')
            ->where('t1.status','1');
            if (count($group_ids) > 0) {
                $data->whereIn('t1.user_group_id', $group_ids);
            }

            return $data->distinct()->get();
        } else {
            $data = ProgramUsers::select('program_users.id', 'program_users.account_id', 'first_name', 'last_name', 'email', 'profile_image', 'image_path')
            ->where(['program_users.is_active' => 1])
            ->where(function($query) use ($search){
                $query->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%")
                ->orWhereRaw("concat(first_name, ' ', last_name) LIKE '%{$search}%' ");
            });

            return $data->distinct()->get();
        }
    }

    /**
     * @param Program $program
     * @param ProgramUsersRequest $request
     *
     * @return mixed
     */
    public function store(Program $program, ProgramUsersRequest $request)
    {
        try {

            $account = Account::create([
                'name'              => $request->username,
                'email'             => $request->email,
                'password'          => $request->password,
                'contact_number'    => $request->contact_number,
                'type'              => 'user',
                'email_verified_at' => null,
                'status'              => 0,
            ]);

            if ($request->group_id) {
                $account->assignRole(Role::findById($request->group_id));
            }

            $programUser =  ProgramUsers::create([
                'program_id'               => $program->id,
                'title'                    => $request->title,
                'first_name'               => $request->first_name,
                'last_name'                => $request->last_name,
                'email'                    => $request->email,
                'username'                 => $request->username,
                'company'                  => $request->company,
                'job_title'                => $request->job_title,
                'address_1'                => $request->address_1,
                'address_2'                => $request->address_2,
                'town'                     => $request->town,
                'postcode'                 => $request->postcode,
                'country'                  => $request->country,
                'telephone'                => $request->telephone,
                'mobile'                   => $request->mobile,
                'date_of_birth'            => $request->date_of_birth,
                'communication_preference' => $request->communication_preference,
                'language'                 => $request->language,
                'account_id'               => $account->id,
                'emp_number'               => $request->emp_number,
                'vp_emp_number'            => $request->vp_emp_number,
                'country_id'               => $request->country_id,
            ]);
            // if($request->emp_type == 'lead'){
            //     $programUser->vp_emp_number = $programUser->id;
            //     $programUser->save();
            // }
            $programUser->save();
            $date = date('Y-m-d h:i:s');

            $check_data = UsersGroupList::where(['account_id'=>$account->id,'user_group_id'=>$request->group_id,'user_role_id'=>$request->role_id])->first();
            if(empty($check_data)){

                $UsersGroupList = new UsersGroupList;
                $UsersGroupList->account_id = $account->id;
                $UsersGroupList->user_group_id = $request->group_id;
                $UsersGroupList->user_role_id = $request->role_id;
                $UsersGroupList->created_at = $date;
                $UsersGroupList->updated_at = $date;
                $UsersGroupList->save();
            }

            $check_lead_data = UsersGroupList::where(['account_id'=>$request->vp_emp_number,'user_group_id'=>$request->group_id,'user_role_id'=>'2'])->first();

            if(empty($check_lead_data)){
                $UsersGroupList = new UsersGroupList;
                $UsersGroupList->account_id = $request->vp_emp_number;
                $UsersGroupList->user_group_id = $request->group_id;
                $UsersGroupList->user_role_id = '2';
                $UsersGroupList->created_at = $date;
                $UsersGroupList->updated_at = $date;
                $UsersGroupList->save();
            }
            return $programUser;

            // if(isset($request->emp_type)){

            // }
        } catch (\Throwable $th) {
            echo $th->getMessage();
            die();
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param ProgramUsersRequest $request
     * @param $id
     */
    public function update($request, $id): void
    {
        $this->repository->update($request->all(), $id);
    }

    /**
     * @param $id
     */
    public function destroy($id): void
    {
        $this->repository->destroy($id);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function search(Request $request)
    {
        return $this->repository->search($request->all());
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function assignUserToGroup(Request $request)
    {
        $role = Role::findById($request->role_id);

        $user = ProgramUsers::find($request->user_id);

        if (empty($user))
            throw ValidationException::withMessages([ 'user' => 'This user is not active or does not exist' ]);

        $account = $user->account;

        return $account->assignRole($role);
    }

    /**
     * @param Request $request
     * @param Program $program
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginatedUsers(Request $request, Program $program)
    {
        return $program->users()->paginate(10);
    }

    public function userProfileBalance($id) {
        $result = UsersPoint::where('user_id', '=', $id)->orderBy('id', 'DESC')->first();

        if($result) {
            return $result->balance;
        } else {
            return 0;
        }
    }

    public function newUserFeedback($requestData) {
        return $this->repository->createNewFeedback($requestData);
    }

    public function userPoints($id) {
        return $this->repository->fetchUserPoints($id);
    }

    public function getAllUsersPieChartData() {
        $UserList = $getUserList = ProgramUsers::orderBy('id', 'desc')->get();

        return $UserList;
    }


}
