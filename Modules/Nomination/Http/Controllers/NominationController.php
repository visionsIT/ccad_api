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
use Modules\Nomination\Models\UserCampaignRole;
use Modules\User\Models\ProgramUsers;

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
                'campaign_id'=>'required|exists:campaign_settings,campaign_id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            CampaignSettings::where('campaign_id',$request->campaign_id)->update(['wall_settings'=>$request->wall_post]);

            return response()->json(['message'=>'Saved successfully.', 'status'=>'success']);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }/****fn ends***/

    public function getWallSettings($campaign_id){
        try{
            $check_campaign = CampaignSettings::where('campaign_id','=',$campaign_id)->first();
            if(!empty($check_campaign)){
                $get_wall_setting = CampaignSettings::select('wall_settings')->where('campaign_id','=',$campaign_id)->first();

               return response()->json(['message'=>'Get Successfully.', 'status'=>'success','wall_settings'=>$get_wall_setting->wall_settings]);exit;
            }else{
                return response()->json(['message'=>'Campaign missing.', 'status'=>'error']);exit;
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }#fn_ends

	public function getCampaignLeadUsers()
	{
        $data = ProgramUsers::where('is_active',1)->get();
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
				$accountIDs 	= $input['account_id'];
				$userRoleIDs 	= $input['user_role_id'];
				if(!empty($accountIDs))
				{					
					for($i=0;$i<count($accountIDs);$i++)
					{
						$account_id = Helper::customDecrypt($accountIDs[$i]);
						$check = UserCampaignRole::where(array("campaign_id" => $input['campaign_id'],"account_id" => $account_id,"user_role_id" => $userRoleIDs[$i],'deleted_at' => null))->exists();
						
						if(empty($check))
						{
							$saveArray = array();
							$saveArray['campaign_id'] 	= $input['campaign_id'];
							$saveArray['account_id'] 	= $account_id;
							$saveArray['user_role_id'] 	= $userRoleIDs[$i];
							UserCampaignRole::create($saveArray);
						}
					}
					return response()->json(['message'=>'Saved successfully.', 'status'=>'success']);
				}
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
				$id = UserCampaignRole::find(array("campaign_id" => $input['campaign_id'],"account_id" => $input['account_id'],"user_role_id" => $input['user_role_id'],'deleted_at' => null));
				$id->softDeletes();
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
				return $data;	
			}
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }	
		
    }
	
}
