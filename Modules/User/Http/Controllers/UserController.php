<?php namespace Modules\User\Http\Controllers;

use Carbon\Carbon;
use App\Http\Resources\ProgramUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\User\Http\Requests\GetRegisteredUsersRequest;
use Modules\User\Http\Services\UserService;
use \Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Program\Models\Program;
use Modules\User\Http\Requests\ProgramUsersRequest;
use Modules\User\Transformers\UserTransformer;
use Modules\User\Transformers\UserGroupTransformer;
use Modules\User\Transformers\RoleuserTransformer;
use Modules\User\Transformers\EmployeesTransformer;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\UsersGroupList;
use Modules\User\Models\SsoLoginDetails;
use Modules\User\Models\UserRoles;
use Modules\Account\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Account\Models\Permission;
use Maatwebsite\Excel\Facades\Excel;
use Modules\User\Exports\UserExport;
use Validator;
use DB;
use File;
use Illuminate\Support\Facades\Mail;
use Modules\User\Models\UserNotifications;
use Modules\User\Transformers\UserNotificationTransformer;
use Modules\User\Transformers\UserNotificationDetailTransformer;
use Helper;
use Modules\User\Models\VpempNumberLog;

class UserController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
		$this->middleware('auth:api');
        // $this->middleware(['paginatedUsers','getGroupLeadUsers']);
        // $this->middleware('auth:api', ['paginatedUsers','getGroupLeadUsers']);
    }


    /*******************
    fn to get all users
    *******************/
    public function getAllUsers($campaign_id = null){
        $param = '';

        if($campaign_id){
            $campaign_idv = $campaign_id;
        }else{
            $campaign_idv = '';
        }

        if(isset($request->order) || isset($request->col)){
            $param = [
                'search' => ($request->search)?$request->search:'',
                'column' => ($request->col)?$request->col:'id',
                'order' => ($request->order)?$request->order:'desc',
            ];
            $users = $this->service->getAllUsers($param,$campaign_idv);
        } else {
            $users = $this->service->getAllUsers('',$campaign_idv);
        }
        $userList = fractal($users, new UserTransformer());

        if(isset($request->pid) && $request->pid == 1){
            $file = Carbon::now()->timestamp.'-AllUserData.xlsx';
            $path = public_path('uploaded/'.$request->pid.'/users/csv/exported/'.$file);
            $responsePath = 'uploaded/'.$request->pid.'/users/csv/exported/'.$file;
            Excel::store(new UserExport($param), 'uploaded/'.$request->pid.'/users/csv/exported/'.$file, 'real_public');
            return response()->json([
                'file_path' => url($responsePath),
            ]);
        } else {
            return $userList;
        }
    }

    public function getSearchedUsers(Request $request) {
        $data = $this->service->getSearchedUsers($request);
        $data = $data->toArray();
        foreach($data as $key => $value) {
            $data[$key]['account_id'] = Helper::customCrypt($value['account_id']);
            $id = $data[$key]['id'];
            unset($data[$key]['id']);
            $data[$key]['id'] = Helper::customCrypt($id);
        }
        return  response()->json([
            'users' => $data,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(Request $request) {

        $param = '';
        if(isset($request->order) || isset($request->col)){
            $param = [
                'search' => ($request->search)?$request->search:'',
                'column' => ($request->col)?$request->col:'id',
                'order' => ($request->order)?$request->order:'desc',
            ];
            $users = $this->service->get($param);
        } else {
            $users = $this->service->get();
        }
        $userList = fractal($users, new UserTransformer());

        if(isset($request->pid) && $request->pid == 1){
            $file = Carbon::now()->timestamp.'-AllUserData.xlsx';
            $path = public_path('uploaded/'.$request->pid.'/users/csv/exported/'.$file);
            $responsePath = 'uploaded/'.$request->pid.'/users/csv/exported/'.$file;
            Excel::store(new UserExport($param), 'uploaded/'.$request->pid.'/users/csv/exported/'.$file, 'real_public');
            return response()->json([
                'file_path' => url($responsePath),
            ]);
        } else {
            return $userList;
        }
    }



    /**
     * @param Program $program
     * @param ProgramUsersRequest $request
     *
     * @return Fractal
     * @throws \Throwable
     */
    public function store(Program $program, ProgramUsersRequest $request): Fractal
    {
        $user = $this->service->store($program, $request);

        return fractal($user, new UserTransformer());
    }

    /**
     * @param $id
     *
     * @return Fractal
     */
    public function show($id): Fractal
    {

        try{
            $id = Helper::customDecrypt($id);
            $user = $this->service->find($id);

            return fractal($user, new UserTransformer());
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check id and try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    /**
     * @param ProgramUsersRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ProgramUsersRequest $request, $id): JsonResponse
    {
        try{
            $id = Helper::customDecrypt($id);
            $this->service->update($request, $id);

            return response()->json([ 'message' => 'Data has been successfully updated','status'=>'Success' ]);
        }catch (\Throwable $th) {
            return response()->json(['status'=>'error','message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try{
            $id = Helper::customDecrypt($id);
            $this->service->destroy($id);

            return response()->json([ 'message' => 'Data has been successfully deleted' ]);
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    /**
     * @param Request $request
     *
     * @return Fractal
     */
    public function search(Request $request): Fractal
    {

        $users = $this->service->search($request);
        return fractal($users, new UserTransformer());
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function assignUserToGroup(Request $request)
    {
        return $this->service->assignUserToGroup($request);
    }

    /********************

        Group assignment user

    ********************/
    public function groupAssignmentUser(Request $request){

        try{

            $rules = [
                'group_id' => 'required|integer|exists:roles,id',
                'role_id' => 'required|integer|exists:user_roles,id',
                'account_id' => 'required|integer|exists:accounts,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $date = date('Y-m-d h:i:s');

            $check_data = UsersGroupList::where(['account_id'=>$request->account_id,'user_group_id'=>$request->group_id,'user_role_id'=>$request->role_id])->first();
            // $check_data = UsersGroupList::where(['account_id'=>$request->account_id,'user_role_id'=>$request->role_id])->first();
            if(!empty($check_data) && $check_data->user_role_id == 1){
                return response()->json(['message'=>'Selected user already exists in another group with same role. Please select other role to add selected user in this group.', 'status'=>'error']);exit;
            }else{
                $UsersGroupList = new UsersGroupList;
                $UsersGroupList->account_id = $request->account_id;
                $UsersGroupList->user_group_id = $request->group_id;
                $UsersGroupList->user_role_id = $request->role_id;
                if(isset($request->status) && $request->status != ''){
                    $UsersGroupList->status = $request->status;
                }
                $UsersGroupList->created_at = $date;
                $UsersGroupList->updated_at = $date;
                $UsersGroupList->save();

                return response()->json(['message'=>'Saved successfully.', 'status'=>'success']);exit;
            }

        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error' ,'errors' => $th->getMessage()]);
        }

    }


    /************************
    fn to get users groupwise
    ************************/
    public function groupUsersList(Request $request,$role_id = null, $group_id = null){

        if($role_id == null || !is_numeric($role_id) || $group_id == null || !is_numeric($group_id)){
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }else{

            try{
                $param = '';
                if(isset($request->search)){
                    $param = [
                        'search' => ($request->search)?$request->search:'',
                    ];
                    $userdata = $this->service->groupUserList($param,$role_id,$group_id);
                } else {
                    $userdata = $this->service->groupUserList($param,$role_id,$group_id);
                }

                $userList = fractal($userdata, new UserGroupTransformer());
                return $userList;

            }catch (\Throwable $th) {
                return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error' ,'errors' => $th->getMessage()]);
            }

        }
    }

    /********************************************************
    fn to get all users except the users of provided group id
    *********************************************************/
    public function groupExcludeUsersList($group_id = null){

        if($group_id == null || !is_numeric($group_id)){
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }else{

            try{
                $userdata = UsersGroupList::select('account_id')->where('user_group_id',$group_id)->get()->toArray();

                $excludeUsers = array();
                foreach ($userdata as $userExclude)
                {
                    $excludeUsers[] = $userExclude['account_id']; // Label
                }

                $get_users = Account::whereNotIn('id',$excludeUsers)->get()->toArray();

                if(!empty($get_users)){
                    foreach($get_users as $key => $value){
                        $get_users[$key]['account_id'] = $value['id'];
                    }
                    return response()->json(['message'=>'Data successfully.', 'status'=>'success','data'=>$get_users]);exit;
                }else{
                    return response()->json(['message'=>"No Record Found", 'status'=>'success']);
                }

            }catch (\Throwable $th) {
                return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error' ,'errors' => $th->getMessage()]);
            }

        }
    }

    /**
     * Display a listing of the current logged-in employees and it's teams.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function getAllEmployees(Request $request): Fractal
    {
        $account = \Auth::user();
        $accdpt =  $account->def_dept_id;

        $users = null;//where('program_users.program_id', $request['program_id'])

        //if($account->hasRole('ADPortVP') || $account->hasRole('ADPortHR')){

            $users = ProgramUsers::join('accounts', 'program_users.account_id', '=', 'accounts.id')
            ->where('accounts.def_dept_id', '=', $accdpt);
            // ->where('program_users.first_name', 'like', '%' . $request['employeeName'] . '%')
            // ->orWhere('program_users.last_name', 'like', '%' . $request['employeeName'] . '%');

            $users = $users->get();
       // }

        return fractal($users, new EmployeesTransformer());

        // if(!empty($request->get('team_name')) || !empty($request->get('team_id'))){
        //     $users = ProgramUsers::join('accounts', 'program_users.account_id', '=', 'accounts.id')
        //     ->join('teams_accounts_link', 'teams_accounts_link.account_id', '=', 'accounts.id')
        //     ->join('teams', 'teams.id', '=', 'teams_accounts_link.team_id');


        //     if(!empty($request->get('team_name')))
        //     {
        //         $users = $users->where('teams.name', 'like', '%' . $request['team_name'] . '%');
        //     }
        //     if(!empty($request->get('team_id')))
        //         $users = $users->where('teams.id', $request['team_id']);

        //     if(!empty($request->get('program_id')))
        //         $users = $users->where('program_users.program_id', $request['program_id']);

        //     if(!empty($request->get('employeeName'))){
        //         $users = $users->where('program_users.first_name', 'like', '%' . $request['employeeName'] . '%')
        //                         ->orWhere('program_users.last_name', 'like', '%' . $request['employeeName'] . '%');
        //     }

        //     $users = $users->get();



        //      $collection = collect($users)->map(function ($model) {
        //         return $model->account_id;
        //     });
        //      $users = ProgramUsers::whereIN('account_id',$collection)->get();

        // }else{

    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function test(Request $request)
    {
        if (!Gate::allows('user/test', $request )) {
            return;
        }

        $response = [
            'status'    =>  false,
            'message'    =>  'Invalid Access Token!',
        ];
        $loggedin_user = $request->user();
        if($loggedin_user){

            $response = [
                'user'      =>  [
                    'id'        =>  $loggedin_user->id,
                    'email'     =>  $loggedin_user->email
                    ],
                'status'    =>  true,
                'message'    =>  'Request Successfull!',
                ];

            if($loggedin_user->hasRole('ADPortVP')){

            $response = [
                'user'      =>  [
                    'id'        =>  $loggedin_user->id,
                    'email'     =>  $loggedin_user->email
                    ],
                'status'    =>  true,
                'HasRole'   => 'ADPortVP',
                'message'    =>  'Request Successfull!',
                ];
            }
        }
        return response()->json($response);
    }

    /**
     * @param GetRegisteredUsersRequest $request
     * @param Program $program
     * @return AnonymousResourceCollection
     */
    public function paginatedUsers(GetRegisteredUsersRequest $request, Program $program)
    {

        return ProgramUser::collection($this->service->paginatedUsers($request, $program));
    }

    public function getUsersRoleWise($program, $role_id){

        $finalArray['leads'] = [];
        $finalArray['employees'] = [];
        $users = DB::table('model_has_roles')->select('program_users.*')->where(['role_id' => $role_id, 'program_id'=>$program])->join('program_users', 'program_users.account_id', '=', 'model_id')->orderBy('model_id', 'DESC')->get()->toArray();

        if(count($users)){
            foreach($users as $User){
                if($User->id == $User->vp_emp_number){
                    $finalArray['leads'][] =[
                        'user_id' => $User->id,
                        'account_id' => $User->account_id,
                        'name' => $User->first_name . ' ' . $User->last_name,
                        'email' => $User->email,
                        'username' => $User->username,
                        'emp_number' => $User->emp_number,
                        'vp_emp_number' => $User->vp_emp_number,
                        'is_active' => $User->is_active
                    ];
                } else {
                    $finalArray['employees'][] = [
                        'user_id' => $User->id,
                        'account_id' => $User->account_id,
                        'name' => $User->first_name . ' ' . $User->last_name,
                        'email' => $User->email,
                        'username' => $User->username,
                        'emp_number' => $User->emp_number,
                        'is_active' => $User->is_active
                    ];
                }
            }
        }
        return json_encode($finalArray, true);
    }

    public function updateUserStatus(Request $request){
        try {
            $request['user_id'] = Helper::customDecrypt($request['user_id']);
            $request['account_id'] = Helper::customDecrypt($request['account_id']);
            $rules = [
                'user_id' => 'required|integer|exists:program_users,id',
                'account_id' => 'required|integer|exists:accounts,id',
                'change_status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $programUser = ProgramUsers::find($request->user_id);
            $programUser->is_active = $request->change_status;
            $programUser->save();

            $programUser = Account::find($request->account_id);
            $programUser->status = $request->change_status;
            $programUser->save();

            return response()->json(['message' => 'Status has been changed successfully.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }
    /************************
    fn to change status from
    groups******************/
    public function changeGroupUserStatus(Request $request){
        try {
            $rules = [
                'group_id' => 'required|integer|exists:roles,id',
                'account_id' => 'required|integer|exists:accounts,id',
                'role_id' => 'required|integer|exists:user_roles,id',
                'change_status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $check_record = UsersGroupList::where(['account_id'=>$request->account_id,'user_group_id'=>$request->group_id,'user_role_id'=>$request->role_id])->update(['status'=>$request->change_status]);


            return response()->json(['message' => 'Status has been changed successfully.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    public function updateUserInfo(Request $request, $id){
        try {
            $id = Helper::customDecrypt($id);
            $request['first_name'] = filter_var($request->first_name, FILTER_SANITIZE_STRING);
            $request['last_name'] = filter_var($request->last_name, FILTER_SANITIZE_STRING);
            $request['job_title'] = filter_var($request->job_title, FILTER_SANITIZE_STRING);

            if(isset($request->username)){
                $request['username'] = filter_var($request->username, FILTER_SANITIZE_STRING);
            }

            $rules = [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'language' => 'required',
                //'company' => 'required',
                //'job_title' => 'required|string',
                //'vp_emp_number' => 'required|integer|exists:accounts,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $this->service->update($request, $id);

            $programUser = $this->service->find($id);
            $updateInfo = ['name' => $request->first_name.' '.$request->last_name];
            DB::table('accounts')->where('id', $programUser->account_id)->update($updateInfo);

            return response()->json([ 'message' => 'Data has been updated successfully','status'=>'Success' ]);
        }  catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.','status'=>'error', 'errors' => $th->getMessage()], 402);
        }
    }

    public function getUserPoints($id) {
        return $this->service->userPoints($id);
    }

    public function feedPieChart(Request $request) {
        $users = $this->service->getAllUsersPieChartData();

        $totalUser = count($users);

        $activeUser = [];
        $inactiveUser = [];

        for($i = 0; $i < $totalUser; $i++) {
            if ($users[$i]->is_active === 1) {
                array_push($activeUser, $users[$i]);
            } else {
                array_push($inactiveUser, $users[$i]);
            }
        }

        $activeUsers = count($activeUser) / $totalUser * 100;
        $inactiveUsers = count($inactiveUser) / $totalUser * 100;

        return response()->json([ 'status' => true, 'active_users' => number_format($activeUsers, 2), 'inactive_users' => number_format($inactiveUsers, 2) ]);
    }

    public function getssoLoginDetails(Request $request){

        try{
            $get_sso = SsoLoginDetails::get()->first();
            $sso_data = array();
            if(!empty($get_sso)){
                $sso_data['entity_id'] = $get_sso->entity_id;
                $sso_data['sso_url'] = $get_sso->sso_url;
                $sso_data['sl_url'] = $get_sso->sl_url;
                $sso_data['x509'] = $get_sso->x509;
            }
            return response()->json(['message'=>'Get data successfully.', 'status'=>'success','data'=>$sso_data]);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }


    /**********************
    Save SSO Login Details
    **********************/
    public function saveSsoLoginDetails(Request $request) {
        $rules = [
            'entity_id' => 'required',
            'sso_url' => 'required',
            'sl_url' => 'required',
            'x509' => 'required',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        try{
            $get_sso = SsoLoginDetails::get()->first();
            if(empty($get_sso)){
                SsoLoginDetails::Create([
                    'entity_id' => $request->entity_id,
                    'sso_url' => $request->sso_url,
                    'sl_url' => $request->sl_url,
                    'x509' => $request->x509,
                ]);
            }else{
                SsoLoginDetails::where('id',$get_sso->id)->Update([
                    'entity_id' => $request->entity_id,
                    'sso_url' => $request->sso_url,
                    'sl_url' => $request->sl_url,
                    'x509' => $request->x509,
                ]);
            }

            return response()->json(['message'=>'SSO Settings added successfully.', 'status'=>'success']);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }

    }

    /***************************
    get all users of group except
    simple user and admin
    *****************************/
    public function getGroupLeadUsers($group_id = null){
        if($group_id == null){
            return response()->json(['message'=>'Please provide Group id.', 'status'=>'error']);exit;
        }else{

            $data = UsersGroupList::where('user_group_id',$group_id)->where('user_role_id','!=',1)->where('user_role_id','!=',4)->where('user_role_id','!=',5)->get();

            $userList = fractal($data, new UserGroupTransformer());
            return $userList;
        }
    }/****fn_ends****/

    public function getLeadUsers() {
        $data = UsersGroupList::whereHas('programUserData')->where('user_role_id','!=',4)->where('user_role_id','!=',5)->get();
        $userList = fractal($data, new UserGroupTransformer());
        return $userList;
    }

    /**********************
    upload user profile pic
    **********************/
    public function uploadUserProfilePic(Request $request){
        try{
            $request['account_id'] = Helper::customDecrypt($request['account_id']);
            $rules = [
                'account_id' => 'required|integer|exists:accounts,id',
                'profile_pic' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $request->validate([
                    'profile_pic' => 'file||mimes:jpeg,png,jpg',
                ]);
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $path = 'uploaded/user_profile_pics/';
                if(!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0777, true, true);
                }
                $randm = rand(10,1000);
                $newname = $randm.time().'-userProfile-'.$filename.'.'.$file_ext;
                $newname = str_replace(" ","_",$newname);
                $destinationPath = public_path($path);
                $file->move($destinationPath, $newname);

                #delete_user_prev_image_from_folder
                $get_image = ProgramUsers::select('profile_image')->where('account_id',$request->account_id)->first();

                if(!empty($get_image)){
                    if($get_image->profile_image != '' && $get_image->profile_image != null && $get_image->profile_image != 'null'){
                        File::delete($destinationPath.$get_image->profile_image);
                    }
                }

                ProgramUsers::where('account_id',$request->account_id)->update(['profile_image'=>$newname,'image_path'=>$path]);

                return response()->json(['message'=>'Profile Image saved successfully.', 'status'=>'success']);exit;
            }else{
                return response()->json(['message'=>'please provide Profile Image.', 'status'=>'error']);exit;
            }
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check account_id and try again.', 'errors' => $th->getMessage()], 402);
        }

    }/****fn_ends_here***/


    /******************
    get user profile pic
    ********************/
    public function getUserProfilePic($account_id = null){
        try{
            $account_id = Helper::customDecrypt($account_id);
            $get_image = ProgramUsers::select('profile_image','image_path')->where('account_id',$account_id)->first();

            if(!empty($get_image)){
                if($get_image->profile_image != '' && $get_image->profile_image != null && $get_image->profile_image != 'null'){

                    $profile_img = '/'.$get_image->image_path.$get_image->profile_image;
                    $user_profile_img = url($profile_img);

                    return response()->json(['message'=>'Profile Image get successfully.', 'status'=>'success','profile_image'=>$user_profile_img]);exit;
                }
            }

            return response()->json(['message'=>'Image not Found.', 'status'=>'error']);exit;
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please check account_id and try again.', 'errors' => $th->getMessage()], 402);
        }
    }/******fn_ends_here******/

    public function userBlockUnblock($b_status = null, $account_id = null){

        try{
            if($b_status == null || $account_id == null){
                return response()->json(['message'=>'Please provide user id and block status.', 'status'=>'error']);exit;
            }else{
                $account_id = Helper::customDecrypt($account_id);
                $account = ProgramUsers::where('account_id',$account_id)->first();
                if(!empty($account)){

                    $data = [
                        'email' => $account->email,
                        'name' => $account->first_name,
                    ];

                    if($b_status == 0){
                        Account::where('id',$account_id)->update(['login_attempts'=>0]);

                        $emailcontent["template_type_id"] =  '12';
                        $emailcontent["dynamic_code_value"] = array($data['name']);
                        $emailcontent["email_to"] = $data["email"];
                        $emaildata = Helper::emailDynamicCodesReplace($emailcontent);

                        return response()->json(['message'=>'User Un-Blocked Successfully.', 'status'=>'success']);exit;
                    }else{
                        Account::where('id',$account_id)->update(['login_attempts'=>3]);

                        $emailcontent["template_type_id"] =  '11';
                        $emailcontent["dynamic_code_value"] = array($data['name']);
                        $emailcontent["email_to"] = $data["email"];
                        $emaildata = Helper::emailDynamicCodesReplace($emailcontent);

                        return response()->json(['message'=>'User Blocked Successfully.', 'status'=>'success']);exit;
                    }
                }else{
                    return response()->json(['message'=>'Wrong account id.', 'status'=>'error']);exit;
                }

            }
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }



    }#unblock_fn_ends

    /************************
    fn to get All Admin Users
    ************************/
    public function getUserListing($admin = null){
        try{
            if($admin == null){
                return response()->json(['message'=>'Please provide 1 for admin']);
            }else{
                if($admin == 1){
                    $get_users = UsersGroupList::where('user_role_id','4')->orWhere('user_role_id','5')->paginate(20);
                }else{
                    $get_users = UsersGroupList::where('user_role_id','!=','4')->where('user_role_id','!=','5')->get();
                }
                $userList = fractal($get_users, new UserGroupTransformer());
                return $userList;
            }

        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }/****admin_user_api_ends***/

    /************************
    fn to count logged in and
    blocked users
    ************************/
    public function countUsersBlockedAndLogin(){
        try{
            $users = array();
            $count_blocked = Account::where('login_attempts',3)->count();
            $count_login = Account::whereNotNull('last_login')->count();

            $notAdmin = ProgramUsers::select('program_users.account_id')->join('accounts', 'program_users.account_id', '=', 'accounts.id')->join('users_group_list', 'accounts.id', '=', 'users_group_list.account_id')->where('users_group_list.user_role_id','!=',4)->groupBy('users_group_list.account_id')->get();

            $count_notAdmin = count($notAdmin);

            $users['blocked_users'] = $count_blocked;
            $users['login_users'] = $count_login;
            $users['not_admin'] = $count_notAdmin;

            return response()->json(['message' => 'Count Get Successfully', 'status'=>'success','data'=>$users]);

        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }/*****count_fn_ends_here****/

    public function userNotifications($account_id = null)
    {
        try{
            if($account_id == null || $account_id == ''){
                return response()->json(['message' => 'Please provide account id.','status'=>'error']);
            }
            $getUserNotifications = UserNotifications::where('receiver_account_id', $account_id)->orderBy('id', 'desc')->get();
            return fractal($getUserNotifications, new UserNotificationTransformer());
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

    /******************************************
    fn to change status of notification to read
    *******************************************/
    public function userNotificationsStatus(Request $request){
        try{
            $request['notification_id'] = Helper::customDecrypt($request->notification_id);
             $rules = [
                 'notification_id' => 'required|integer|exists:user_notifications,id',
             ];
 
             $validator = \Validator::make($request->all(), $rules);
 
             if ($validator->fails())
                 return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
 
             $getUserNotifications = UserNotifications::where('id', $request->notification_id)->update(['read_status'=>'1']);
             return response()->json(['message' => 'Status Changed Successfully.', 'status' => 'success']);
 
         }catch (\Throwable $th) {
             return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
         }

    }/****fn_ends*****/

    /************************
    fn to count notifications
    *************************/
    public function countUserNotifications($account_id = null){
        try{
            if($account_id == null || $account_id == ''){
                return response()->json(['message' => 'Please provide account id.','status'=>'error']);
            }

            $account_id = Helper::customDecrypt($account_id);
            $count_Notifications = UserNotifications::where('receiver_account_id', $account_id)->count();
            return response()->json(['data'=>$count_Notifications,'message' => 'Get count Successfully.', 'status' => 'success']);
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }/***fn_ends_here***/

    /**notification details**/
    public function notificationDetail($notification_id = null){
        try{
            
            if($notification_id == null || $notification_id == ''){
                return response()->json(['message' => 'Please provide notification id.','status'=>'error']);
            }
            
            $notification_id = Helper::customDecrypt($notification_id);
            $notification_detail = UserNotifications::where('id', $notification_id)->first();

            return fractal($notification_detail, new UserNotificationDetailTransformer());
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }/******fn_ends*******/


    

}
