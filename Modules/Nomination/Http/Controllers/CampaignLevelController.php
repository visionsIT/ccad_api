<?php

namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Http\Requests\ValueSetRequest;
use Modules\Nomination\Transformers\ValueSetTransformer;
use Modules\Nomination\Repositories\ValueSetRepository;
use Modules\Nomination\Http\Services\NominationTypeService;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\CampaignTypes;
use Modules\Nomination\Models\CampaignSettings;
use Modules\User\Models\ProgramUsers;
use DB;
class CampaignLevelController extends Controller
{
    private $repository;
    private $types_services;

    public function __construct(ValueSetRepository $repository,NominationTypeService $types_services)
    {
        $this->repository = $repository;
        $this->types_services = $types_services;
		$this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
     
        $types = $this->repository->get();
        return fractal($types, new ValueSetTransformer);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function myorders($account_id): Fractal
    {
        $types = $this->repository->UserOrders($account_id);
        return fractal($types, new ValueSetTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ValueSetRequest $request
     * @return Fractal
     */
    public function store(ValueSetRequest $request)
    {

        try {
        $Category = $this->repository->create($request->all());

        return fractal($Category, new ValueSetTransformer);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

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

        return fractal($Category, new ValueSetTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param ValueSetRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ValueSetRequest $request, $id): JsonResponse
    {
        $this->repository->update($request->all(), $id);
        return response()->json(['message' => 'Category Updated Successfully']);
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

        return response()->json(['message' => 'Category Trashed Successfully']);
    }

    public function updateType(ValueSetRequest $request, $id): JsonResponse
    {
        ValueSet::where('id', $id)->update($request->all());

        return response()->json(['message' => 'Nomination type Updated Successfully']);
    }

    public function addNewType(ValueSetRequest $request)
    {
        try {
            $Category = ValueSet::create([
                'name' => $request->name,
                'program_id' => $request->program_id,
                'description' => $request->description,
                'campaign_type_id' => $request->campaign_type_id,
            ]);
            $campaign_id = $Category->id; 

            $campain_setting = CampaignSettings::create([
                    'campaign_id' => $campaign_id,
                    'send_multiple_status' => 0,
                    'approval_request_status' => 0,
                    'level_1_approval' => 0,
                    'level_2_approval' => 0,
                    'budget_type' => 2,
                    'points_allowed' => 1,
                    's_eligible_user_option' => 0
            ]);
            return fractal($Category, new ValueSetTransformer);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        }
    }

    public function updateStatus(Request $request) {
        try {
            $rules = [
                'id' => 'required|integer|exists:value_sets,id',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $valueSet = $this->repository->find($request->id);
            $valueSet->status = $request->status;
            $valueSet->save();

            return response()->json(['message' => 'Status has been changed successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    /**
     * API: Display a listing of the Campaign Type.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function campaignList()
    {
        //$types = $this->repository->get();

        $campaign_type = CampaignTypes::where('status', '1')->get();
        return response()->json(["campain_list" => $campaign_type]);
    }

    /**
     * @return Get Nomination Type Listing
    */
    public function getNominationType($campaign_type_id =null, $program_user_id = null){

        if($campaign_type_id == null){
            return response()->json(["message" => "Please provide campaign id","status"=>"error"]);
        }else if($program_user_id == null){
            return response()->json(["message" => "Please provide program user id","status"=>"error"]);
        }else{

            $finalData = [];
            $check_user = ProgramUsers::where(['id'=>$program_user_id,'is_active'=>1])->first();
            if(!empty($check_user)){

                $id = $check_user->account_id;
                $roles = DB::table('users_group_list')->where('account_id', $check_user->account_id)->join('user_roles', 'user_roles.id', '=', 'users_group_list.user_role_id')->get();

                $max_role_id = 0;
                $finalArray = [];
                $campaign_type = ValueSet::select('id','name')->where('campaign_type_id', $campaign_type_id)->where('status', '1')->get();
                
                foreach($campaign_type as $key=>$campaign){
                    $usereligibility = '0';
                    foreach($roles as $obj)
                    {
                        $role_id = $obj->user_role_id;
                        $camSett = DB::table('campaign_settings')->where(['campaign_id'=>$campaign->id])->first();

                        if(!empty($camSett)){
                            if($camSett->s_eligible_user_option == '0'){
                                #all_users
                                $usereligibility = '1';
                            }else if($camSett->s_eligible_user_option == '1'){
                                #levelwise
                                if($role_id == 3){//L2
                                    if($camSett->s_level_option_selected == '1' || $camSett->s_level_option_selected == '2'){
                                        $usereligibility = '1';
                                    }
                                }else if($role_id == 2){//L1
                                    if($camSett->s_level_option_selected == '0' || $camSett->s_level_option_selected == '2'){
                                        $usereligibility = '1';
                                    }
                                }

                            }else if($camSett->s_eligible_user_option == '2'){
                                #multiplt_user_groups
                                if($camSett->s_user_ids != null){
                                    $get_user_ids = $camSett->s_user_ids;
                                    $userIds = explode(',',$get_user_ids);
                                    if (in_array($program_user_id, $userIds)) {
                                        $usereligibility = '1';
                                    }
                                }
                                if($camSett->s_group_ids != null){
                                    #check_group_ids
                                    $get_group_ids = $camSett->s_group_ids;
                                    $groupIds = explode(',',$get_group_ids);
                                    
                                    foreach($groupIds as $grpID){
                                        $check_grps = DB::table('users_group_list')->distinct('user_group_id')->select('user_group_id')->where(['account_id'=>$id,'user_role_id'=>$role_id,'user_group_id'=>$grpID,'status'=>'1'])->get()->toArray();

                                        if(!empty($check_grps)){
                                            $usereligibility = '1';
                                        }
                                    }
                                   
                                }
                            }
                        }
                        
                    }#endif_camsett

                    $finalArray[$key]['id'] = $campaign->id;
                    $finalArray[$key]['name'] = $campaign->name;
                    $finalArray[$key]['userEligibilty'] = $usereligibility;

                }#foreach_ends
                $finalData = $finalArray;
               
                return response()->json(["nomination_list" => $finalData,"status"=>"success"]);
            }else{
                return response()->json(["message" => "User not found or inactive","status"=>"error","nomination_list" => $finalData]);
            }
        }
        
    }


    /***************************
    fn to get all camapign types
    ***************************/
    public function getCampaignTypes(){
        try{
            $campaignTypes = CampaignTypes::select('id','campaign_type')->where('status','=','1')->get();
            return response()->json(["campaign_type_list" => $campaignTypes,"status"=>"success"]);
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }/**fn ends**/

}
