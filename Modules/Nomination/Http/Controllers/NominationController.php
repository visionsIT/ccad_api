<?php

namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Account\Models\Account;
use Modules\Account\Transformers\AccountTransformer;
use Modules\Nomination\Transformers\UserNominationTransformer;
use Modules\Nomination\Transformers\UserNominationTransformerNew;
use Modules\Program\Transformers\UserEcardsTransformer;
use Modules\User\Transformers\UserTransformer;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Http\Requests\NominationRequest;
use Modules\Nomination\Transformers\NominationTransformer;
use Modules\Nomination\Transformers\NominationValueTransformer;
use Modules\Nomination\Repositories\NominationRepository;
use Modules\Nomination\Http\Services\NominationService;
use Modules\Nomination\Models\Nomination;
use Modules\Nomination\Models\CampaignSettings;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\UserNomination;
use DB;
use Helper;
use Modules\Nomination\Transformers\UserCampaignRoleTransformer;
use Modules\Nomination\Transformers\GetUserCampaignRoleTransformer;
use Modules\Nomination\Models\UserCampaignRole;
use Modules\User\Models\ProgramUsers;
use Modules\Nomination\Models\CampaignLike;
use Modules\Nomination\Models\CampaignComment;

class NominationController extends Controller
{
    private $repository;
    private $nomination_service;

    public function __construct(NominationRepository $repository,NominationService $nomination_service)
    {
        $this->repository = $repository;
        $this->nomination_service = $nomination_service;
		//$this->middleware('auth:api');
    }
    /**
    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $order = $this->repository->get();
        return fractal($order, new NominationTransformer);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function NominationValuesIcons()
    {
        $icons_set=[
            "http://backend.hostersstack.com/img/fair.png",
            "http://backend.hostersstack.com/img/eager.png",
            "http://backend.hostersstack.com/img/respond.png",
            "http://backend.hostersstack.com/img/safe.png",
            "http://backend.hostersstack.com/img/innovation.png"];

        return response()->json($icons_set);
    }
 /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function NominationValuesIcons2()
    {
        $icons_set=[
            "http://backend.hostersstack.com/img/fair_.png",
            "http://backend.hostersstack.com/img/eager_.png",
            "http://backend.hostersstack.com/img/respond_.png",
            "http://backend.hostersstack.com/img/safe_.png",
            "http://backend.hostersstack.com/img/innovation_.png"];

        return response()->json($icons_set);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function NominationValues($nomination_id)
    {
        $record = Nomination::where('id', '=', $nomination_id)->first();

        if ($record === null) {
            return response()->json(['message' => 'The given data was invalid.'], 422);
        }else {
            $value_set = $this->repository->find($nomination_id)->value_set;
            $nomination = $this->repository->get_nomination($nomination_id, $value_set);
            return $nomination;
        }
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param NominationRequest $request
     * @return Fractal
     */
    public function store(NominationRequest $request)
    {
        $validator = \Validator::make($request->all(), [
            'value_set' => 'required|exists:value_sets,id',
            'name' => 'required|unique:nominations',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.'], 422);
        }else{
            $Category = $this->repository->create($request->all());
            return fractal($Category, new NominationTransformer);
        }
    }

    /**
     * Show the specified resource.
     *
     * @param $id
     *
     * @return Fractal
     */
    public function show($id): Fractal
    {
        $Category = $this->repository->find($id);
        return fractal($Category, new NominationTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param NominationRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(NominationRequest $request, $id): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'value_set' => 'required|exists:value_sets,id',
            'name' => 'required|unique:nominations,name,'.$id,
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.'], 422);
        }else {
            $this->repository->update($request->all(), $id);
            return response()->json(['message' => 'Updated Successfully']);
        }
    }

    /**
     *
     *  Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);

        return response()->json(['message' => 'Trashed Successfully']);
    }

    public function users($nomination_id)
    {
        $nomination = $this->nomination_service->find($nomination_id);

        if ($nomination->approval_level === 'approval_level_1')
            $users = $this->nomination_service->getFirstLevelApprovalUsers($nomination);
        else
            $users = $this->nomination_service->getSecondLevelApprovalUsers($nomination);


        return fractal($users , new UserTransformer());

    }

    public function NominationWall($nomination_id)
    {
        $queryString = \Illuminate\Support\Facades\Request::get('q');

        $nomination = $this->nomination_service->find($nomination_id);
        if ($nomination->approval_level === 'approval_level_1')
            $users = $this->nomination_service->getFirstLevelWallUsers($nomination, $queryString);
        else
            $users = $this->nomination_service->getSecondLevelWallUsers($nomination, $queryString);

        return fractal($users , new UserNominationTransformerNew());
       
    }

    /*****************************
    get nomination as per campaign
    *****************************/
    public function NominationCampaignWall($campaign_id = null){

        try{
            $queryString = \Illuminate\Support\Facades\Request::get('q');
            $queryString = isset($queryString) && !empty($queryString) ? Helper::customDecrypt($queryString) : NULL;
            $user = $this->nomination_service->getCampaignUSerNomination($queryString,$campaign_id);
            return fractal($user , new UserNominationTransformerNew());
            
            /*$campaignSetting = ValueSet::with(['Campaign_setting'])->where(['id'=>$campaign_id,'status'=>'1'])->first();

            if($campaignSetting->Campaign_setting->wall_settings == '1'){
                #check_approval_Level1
                if($campaignSetting->Campaign_setting->approval_request_status == '1'){
                   if ($campaignSetting->Campaign_setting->level_1_approval == '1'){
                    $users = $this->nomination_service->getCampaignFirstLevelWallUsers($campaignSetting, $queryString);
                   }else{
                    $users = $this->nomination_service->getCampaignSecondLevelWallUsers($campaignSetting, $queryString);
                   }
                    
                        
                } 
            }
            
            return fractal($users , new UserNominationTransformerNew());*/

        }catch (\Exception $e) {
            return response()->json(['data'=>[],'meta'=>[],'message'=>$e->getMessage(), 'status'=>'success']);
        }
        

    }/*********NominationCampaignWall ends here********/

    /**********
    Ecards data
    **********/
    public function NominationEcardWall(){
        try{
            $queryString = \Illuminate\Support\Facades\Request::get('q');
            $user = $this->nomination_service->getCampaignEcards($queryString);
            return fractal($user , new UserEcardsTransformer());
            
        }catch (\Exception $e) {
            return response()->json(['data'=>[],'meta'=>[],'message'=>$e->getMessage(), 'status'=>'success']);
        }
    }

    /**
     * @return Fractal
     */
    public function NominationBadgesWall()
    {
        $accounts = Account::whereHas('badges')->get();

        return fractal($accounts , new AccountTransformer());

    }

    /*************************
    fn to save wall settings
    **************************/
    public function saveWallSettings(Request $request){
        try{

            $rules = [
                'wall_post' => 'required|in:0,1',
				'is_like' => 'required|in:0,1',
                'is_comment' => 'required|in:0,1',
                'campaign_id'=>'required|exists:campaign_settings,campaign_id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            CampaignSettings::where('campaign_id',$request->campaign_id)->update(['wall_settings'=>$request->wall_post,'like_flag'=>$request->is_like,'comment_flag'=>$request->is_comment]);

            return response()->json(['message'=>'Saved successfully.', 'status'=>'success']);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }/****fn ends***/

    public function getWallSettings($campaign_id){
        try{
            $check_campaign = CampaignSettings::where('campaign_id','=',$campaign_id)->first();
            if(!empty($check_campaign)){
                $get_wall_setting = CampaignSettings::select('wall_settings','like_flag','comment_flag')->where('campaign_id','=',$campaign_id)->first();

               return response()->json(['message'=>'Get Successfully.', 'status'=>'success','wall_settings'=>$get_wall_setting->wall_settings,'comment_flag'=>$get_wall_setting->comment_flag,'like_flag'=>$get_wall_setting->like_flag]);exit;
            }else{
                return response()->json(['message'=>'Campaign missing.', 'status'=>'error']);exit;
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }#fn_ends

	public function UpdateLikeFlag(Request $request)
    {
		if(isset($request['account_id']) && !empty($request['account_id']))
		{
			$request['account_id'] =  Helper::customDecrypt($request->account_id);
			
			$validator = \Validator::make($request->all(), [
				'account_id' => 'required|exists:accounts,id',
				'user_nomination_id' => 'required|exists:user_nominations,id',
				'like' => 'required',
			]);

			if ($validator->fails()) {
				return response()->json(['message' => 'The given data was invalid.'], 422);
			}else{
				
				$account_id 		= $request['account_id'];
				$user_nomination_id = $request['user_nomination_id'];
				$like 				= $request['like'];
				
				$array['account_id'] 			= $account_id;
				$array['user_nomination_id'] 	= $user_nomination_id;
				$array['is_like'] 				= $like;
					
				$where = array('account_id' => $account_id,'user_nomination_id' => $user_nomination_id);
				$check = CampaignLike::where($where)->exists();
				if(!empty($check))
				{
					CampaignLike::where($where)->update(array('is_like' => $like));
				}
				else
				{
					CampaignLike::create($array);
				}
				
				return response()->json(['message' => 'Data Successfully Updated'], 200);
			}
		}
		else
		{
			return response()->json(['message' => 'Account ID is missing.'], 422);
		}
		
    }
	
	public function AddComment(Request $request)
    {
		if(isset($request['account_id']) && !empty($request['account_id']))
		{
			$request['account_id'] =  Helper::customDecrypt($request->account_id);
		
			$validator = \Validator::make($request->all(), [
				'account_id' => 'required|exists:accounts,id',
				'user_nomination_id' => 'required|exists:user_nominations,id',
				'comments' => 'required',
			]);

			if ($validator->fails()) {
				return response()->json(['message' => 'The given data was invalid.'], 422);
			}else{
				
				$pri_id 			= $request['id'];
				$account_id 		= $request['account_id'];
				$user_nomination_id = $request['user_nomination_id'];
				$comments 			= $request['comments'];
				
				$array['account_id'] 			= $account_id;
				$array['user_nomination_id'] 	= $user_nomination_id;
				$array['comments'] 				= $comments;
					
				if(!empty($pri_id))
				{
					$where = array('id' => $pri_id);
					$check = CampaignComment::where($where)->exists();
					if(!empty($check))
					{
						CampaignComment::where($where)->update(array('comments' => $comments));
					}
				}
				else
				{
					CampaignComment::create($array);
				}
				
				return response()->json(['message' => 'Data Successfully Updated'], 200);
			}
		}
		else
		{
			return response()->json(['message' => 'Account ID is missing.'], 422);
		}
		
    }
	
	public function getCampaignLeadUsers()
	{
        $data = ProgramUsers::where('program_users.is_active',1)
							->join('users_group_list','users_group_list.account_id','program_users.account_id')
							->where('user_role_id','!=',4)->where('user_role_id','!=',5)
							->groupBy('program_users.account_id')
							->get('program_users.*');
		$userList = fractal($data,new UserCampaignRoleTransformer());
        return $userList;
    }
	
	public function SaveCampaignRoles(Request $request)
	{
		$input = $request->all();		
        try{

            $rules = [
                'campaign_id' => 'required|exists:value_sets,id',
            ];

            $validator = \Validator::make($input, $rules);

            if ($validator->fails())
			{
				 return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
			}
            else
			{
				$accountIDs 	= (isset($input['account_id']) && !empty($input['account_id'])) ? $input['account_id'] : false;
				$userRoleIDs 	= (isset($input['user_role_id']) && !empty($input['user_role_id'])) ? $input['user_role_id'] : false;
				
				$ApiCheck 		= (!empty($input) && isset($input['l1_approver_flag'])) ? "L1" : "L2";
				$UserRoleID		= 3;
				if(!empty($ApiCheck) && $ApiCheck == "L1")
				{
					CampaignSettings::where('campaign_id',$request['campaign_id'])->update(['l1_approver'=>$request['l1_approver_flag']]);
					$UserRoleID		= 2;
				}
				
				$campaign_id = $input['campaign_id'];
				$RolesArray  = array();
				
				if(!empty($campaign_id))
				{
					$UserCampaignRoleData = UserCampaignRole::where(array("campaign_id" => $campaign_id, "user_role_id" => $UserRoleID ,'deleted_at' => null))
											->select(DB::raw('group_concat(account_id) as Account_IDs'))
											->first();
											
					if(!empty($UserCampaignRoleData) && isset($UserCampaignRoleData->Account_IDs) && !empty($UserCampaignRoleData->Account_IDs))
					{
						$RolesArray = explode(",",$UserCampaignRoleData->Account_IDs);
					}
				}
							
							
				if(!empty($RolesArray))
				{	
					if(!empty($accountIDs))
					{
						$DeleteAccountIDs = $RolesArray;
						for($i=0;$i<count($accountIDs);$i++)
						{
							$account_id = Helper::customDecrypt($accountIDs[$i]);	
							if(in_array($account_id, $RolesArray))
							{
								if (($key = array_search($account_id, $DeleteAccountIDs)) !== false) {
									unset($DeleteAccountIDs[$key]);
								}
							}
						}
						
						if(!empty($DeleteAccountIDs))
						{
							UserCampaignRole::where(array("campaign_id" => $campaign_id,"user_role_id" => $UserRoleID,'deleted_at' => null))
											->whereIn('account_id',$DeleteAccountIDs)
											  ->delete();
						}
					}
					else
					{
						UserCampaignRole::where(array("campaign_id" => $campaign_id,"user_role_id" => $UserRoleID,'deleted_at' => null))
												->delete();
					}
				}
				
				if(!empty($accountIDs))
				{
					for($i=0;$i<count($accountIDs);$i++)
					{
						$account_id = Helper::customDecrypt($accountIDs[$i]);
						$check = UserCampaignRole::where(array("campaign_id" => $campaign_id,"account_id" => $account_id,"user_role_id" => $userRoleIDs[$i],'deleted_at' => null))->exists();
						if(empty($check))
						{
							$saveArray = array();
							$saveArray['campaign_id'] 	= $campaign_id;
							$saveArray['account_id'] 	= $account_id;
							$saveArray['user_role_id'] 	= $userRoleIDs[$i];
							UserCampaignRole::create($saveArray);
						}
					}
				}
				
				return response()->json(['message'=>$ApiCheck .' Data successfully Updated.', 'status'=>'success']);
			}
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }	
    }

	public function SaveCampaignRoles_forSingleUser(Request $request)
	{
		
		$input = $request->all();
		$input['account_id'] = Helper::customDecrypt($input['account_id']);
		
        try{

            $rules = [
                'campaign_id' => 'required|exists:value_sets,id',
                'account_id'=>'required|exists:program_users,account_id',
                'user_role_id'=>'required',
            ];

            $validator = \Validator::make($input, $rules);

            if ($validator->fails())
			{
				 return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
			}
            else
			{
				$check = UserCampaignRole::where(array("campaign_id" => $input['campaign_id'],"account_id" => $input['account_id'],"user_role_id" => $input['user_role_id'],'deleted_at' => null))->first();
				if(empty($check))
				{
					UserCampaignRole::create($input);
					return response()->json(['message'=>'Saved successfully.', 'status'=>'success']);
				}
				else
				{	
					return response()->json(['message'=>'User already exist', 'status'=>'error']);
				}	
			}
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }	
    }
	
	public function DeleteCampaignRoles(Request $request)
	{
		$input = $request->all();
		$input['account_id'] = Helper::customDecrypt($input['account_id']);
		
        try{

            $rules = [
                'campaign_id' => 'required|exists:value_sets,id',
                'account_id'=>'required|exists:program_users,account_id',
                'user_role_id'=>'required',
            ];

            $validator = \Validator::make($input, $rules);

            if ($validator->fails())
			{
				 return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
			}
            else
			{
				UserCampaignRole::where(array("campaign_id" => $input['campaign_id'],"account_id" => $input['account_id'],"user_role_id" => $input['user_role_id'],'deleted_at' => null))
								->delete();
				
				return response()->json(['message'=>'Deleted successfully.', 'status'=>'success']);
			}
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }	
    }

	public function getCampaignRoles(Request $request)
	{
		$input = $request->all();
        try{

            $rules = [
                'campaign_id' => 'required|exists:value_sets,id',
            ];

            $validator = \Validator::make($input, $rules);

            if ($validator->fails())
			{
				 return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
			}
            else
			{
				$data = UserCampaignRole::where('campaign_id',$input['campaign_id'])
											->where('deleted_at',null)
											->join("program_users","program_users.account_id","user_campaign_roles.account_id")
											->join("user_roles","user_roles.id","user_campaign_roles.user_role_id")
											->get(['user_campaign_roles.*','program_users.first_name','program_users.last_name','user_roles.name as role_name']);
				
				$userCampaignRoleList = fractal($data,new GetUserCampaignRoleTransformer());
				return $userCampaignRoleList;
		
				//return $data;	
			}
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }	
		
    }
	
	public function saveUserCampaignRoleSettings(Request $request)
	{
        try{

            $rules = [
                'l1_approver_flag' => 'required|in:0,1',
                'campaign_id'=>'required|exists:campaign_settings,campaign_id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            CampaignSettings::where('campaign_id',$request->campaign_id)->update(['l1_approver'=>$request->l1_approver_flag]);

            return response()->json(['message'=>'Saved successfully.', 'status'=>'success']);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

    public function getUserCampaignRoleSettings(Request $request)
	{
		
		$input = $request->all();
        try{

            $rules = [
                'campaign_id' => 'required|exists:value_sets,id',
            ];

            $validator = \Validator::make($input, $rules);

            if ($validator->fails())
			{
				 return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
			}
            else
			{
				$campaign_id = $input['campaign_id'];
				$check_campaign = CampaignSettings::where('campaign_id','=',$campaign_id)->first();
				if(!empty($check_campaign)){
					$get_l1_approver_setting = CampaignSettings::select('l1_approver')->where('campaign_id','=',$campaign_id)->first();

				   return response()->json(['message'=>'Get Successfully.', 'status'=>'success','l1_approver_setting'=>$get_l1_approver_setting->l1_approver]);exit;
				}else{
					return response()->json(['message'=>'Campaign missing.', 'status'=>'error']);exit;
				}
			} 
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

    /***************************************************************
    api to check user in campaign setting and in vp_emp_num of users
    ****************************************************************/
    public function checkUserInCampaignSetting($account_id = null){

        if($account_id == null){
            return response()->json(['status'=>'error','message' => 'Please provide account id.', 'errors' => $validator->errors()], 422);
        }else{

            $account_id =  Helper::customDecrypt($account_id);

            $account = Account::where('id',$account_id)->get();
            $final_arr = array();
            $admin_tab = 0;
            if(!empty($account)){
                $all_campaigns = ValueSet::where('status','1')->get();
                $reports = null;
                if(!empty($all_campaigns)){
                    foreach($all_campaigns as $key=>$campaign){

                        $data=array();
                        //Check_forL1
                        $campaign_setting = CampaignSettings::where('campaign_id',$campaign->id)->first();
                        if($campaign_setting->l1_approver == '1'){
                            //specific_user
                            $check_role_campaign = UserCampaignRole::where(['account_id'=>$account_id,'user_role_id'=>'2','campaign_id'=>$campaign->id])->first();
                            if(!empty($check_role_campaign)){
                                $reports = DB::table('permissions')
                                ->where('name', 'reports_l1_access')
                                ->first();
                                $admin_tab = 1;
                                $data['id'] = $campaign->id;
                                $data['name'] = $campaign->name;
                            }
                        }else{
                            //vp_emp_number
                            $check_vp_emp = ProgramUsers::where('vp_emp_number',$account_id)->first();
                            if(!empty($check_vp_emp)){
                                $reports = DB::table('permissions')
                                ->where('name', 'reports_l1_access')
                                ->first();
                                $admin_tab = 1;
                                $data['id'] = $campaign->id;
                                $data['name'] = $campaign->name;
                            }
                        }

                        //check_L2
                        //if($admin_tab == 0){
                            $check_roleL2_campaign = UserCampaignRole::where(['account_id'=>$account_id,'user_role_id'=>'3','campaign_id'=>$campaign->id])->first();
                            if(!empty($check_roleL2_campaign)){
                                $reports = DB::table('permissions')
                                ->where('name', 'reports_l2_access')
                                ->first();
                                $admin_tab = 1;
                                $data['id'] = $campaign->id;
                                $data['name'] = $campaign->name;
                            }
                        //}

                        if(!empty($data)){
                            $final_arr[$key] = $data;
                        }
                        
                    }

                    $reportsEmpAccess = DB::table('permissions')
                                ->where('name', 'reports_emp_access')
                                ->first();

                    return response()->json(['status'=>'success','message' => 'Get data successfully.','admin_tab'=>$admin_tab, 'manager_access_reports' => $reports, 'emp_access_reports' => $reportsEmpAccess, 'campaigns'=>$final_arr], 200);

                }else{
                    return response()->json(['status'=>'error','message' => 'No campaign Focund.', 'errors' => $validator->errors()], 422);
                }
            }else{
                return response()->json(['status'=>'error','message' => 'Wrong account id.', 'errors' => $validator->errors()], 422);
            }
            
        }
    }/*****************fn_ends_here****************/

}
