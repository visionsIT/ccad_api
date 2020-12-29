<?php

namespace Modules\Nomination\Http\Controllers;

use Exception;
use Excel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Account\Models\Account;
use Modules\Account\Models\AccountBadges;
use Modules\Nomination\Exports\NominationReportExport;
use Modules\Nomination\Exports\ReportExports;
use Modules\Nomination\Http\Requests\NominationReportExportRequest;
use Modules\Nomination\Http\Requests\UserNomination\GetRequest;
use Modules\Nomination\Http\Services\NominationService;
use Modules\Nomination\Http\Services\UserNominationService;
use Modules\Nomination\Models\Nomination;
use Modules\Nomination\Models\UserNomination;
use Modules\Nomination\Models\UserClaim;
use Modules\User\Http\Services\UserService;
use Spatie\Fractal\Fractal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Http\Requests\UserNominationRequest;
use Modules\Nomination\Http\Requests\TeamNominationRequest;
use Modules\Nomination\Http\Requests\UpdateTeamNominationRequest;
use Modules\Nomination\Http\Requests\RejectTeamNominationRequest;
use Modules\Nomination\Repositories\UserNominationRepository;
use Modules\Nomination\Repositories\NominationRepository;
use Modules\Nomination\Transformers\UserNominationTransformer;
use Modules\Nomination\Transformers\TeamNominationTransformer;
use Modules\Nomination\Transformers\ClaimTypeTransformer;
use Modules\User\Models\ProgramUsers;
use Modules\User\Http\Services\PointService;
use Modules\Account\Http\Services\AccountService;
use \App\Http\Resources\UserNomination as UserNominationResource;
use Illuminate\Foundation\Console\Presets\React;
use Modules\Nomination\Transformers\UserClaimTransformer;
use Modules\Nomination\Models\CampaignSettings;
use Modules\User\Models\UsersPoint;
use Modules\Nomination\Repositories\RippleSettingsRepository;
use Modules\User\Models\RippleBudgetLog;
use Modules\User\Models\UserCampaignsBudget;
use Modules\User\Models\UserCampaignsBudgetLogs;
use Modules\Program\Models\UsersEcards;
use Illuminate\Support\Facades\Mail;
use DB;
class UserNominationController extends Controller
{
    private $repository;
    private $nomination_service;
    private $point_service;
    private $user_nomination_service, $nomination_repository, $user_service;
    private $account_service;

    public function __construct(UserNominationRepository $repository,PointService $point_service,NominationRepository $nomination_repository,NominationService $nominationService,UserNominationService $user_nomination_service, UserService $user_service, AccountService $account_service, RippleSettingsRepository $ripple_repository)
    {
        $this->repository = $repository;
        $this->nomination_repository = $nomination_repository;
        $this->nomination_service = $nominationService;
        $this->user_nomination_service = $user_nomination_service;
        $this->user_service = $user_service;
        $this->point_service = $point_service;
        $this->account_service = $account_service;
        $this->ripple_repository = $ripple_repository;
        $this->middleware('auth:api')->only(['nominations']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(Request $request): Fractal
    {   

        $input = $request->all();

        if( isset($input['filter']) && (int)$input['filter'] > 0 ) {
            $order = $this->repository->filterRecords($input);
        } else {
            $order = $this->repository->getDesc($input['campaignID']);
        }
      
        return fractal($order, new UserNominationTransformer);
    }


    /**
     * @param $account_id
     * @return Fractal
     */
    public function myorders($account_id): Fractal
    {
        $order = $this->repository->UserOrders($account_id);
        return fractal($order, new UserNominationTransformer);
    }

    /**
     * @param UserNominationRequest $request
     * @return Fractal
     * @throws Exception
     */
    public function store(UserNominationRequest $request): Fractal
    {
        
        $newname = '';
        if ($request->hasFile('attachments')) {
            $file = $request->file('attachments');
            $request->validate([
                'attachments' => 'file||mimes:jpeg,png,jpg,pdf',
            ]);
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $newname = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
            $destinationPath = public_path('uploaded/user_nomination_files/');
            $file->move($destinationPath, $newname);
        }
        $request->attachments = $newname;

        $teamNomination = UserNomination::USER_NOMINATION;
        if(isset($request->claim_award)){
            $teamNomination = UserNomination::CLAIM_NOMINATION;
        }

        $user_ids = $request->user;
        $user_id_array = explode(',',$user_ids);

        foreach($user_id_array as $key=>$value){
            $user_nomination = $this->repository->create([
                'user' => (int)$value,
                'account_id' => $request->account_id,
                'campaign_id' => $request->campaign_id,
                'nomination_id' => $request->nomination_id,
                'reason' => $request->reason,
                'value' => $request->value,
                'points' => $request->points,
                'attachments' => $newname,
                'team_nomination' => $teamNomination,
                'nominee_function' => $request->nominee_function,
                'personal_message' => $request->personal_message
            ]);
        }

        $approvals = $this->nomination_service->getApprovalAdmin($user_nomination);

        if( sizeof($approvals) > 0 )
        {
            $this->confirm_nomination($user_nomination, $approvals);
        }

        return fractal($user_nomination, new UserNominationTransformer);
    }

    public function user_EthankyouRecords(Request $request) 
    {
        try{
            $input = $request->all();
            $data = UsersEcards::leftJoin('user_nominations', 'user_nominations.ecard_id', '=', 'users_ecards.id')
            ->select('user_nominations.*', 'users_ecards.*', DB::raw( 'user_nominations.level_1_approval as "Approved for level 1"'), DB::raw( 'user_nominations.level_2_approval as "Approved for level 2"'), DB::raw('DATE_FORMAT(users_ecards.created_at, "%b %d, %Y %h:%i %p") as created_date_time'))
            ->where('users_ecards.campaign_id', $input['campaignID'])
            ->orderBy('users_ecards.id','desc')
            ->with(['nominated_user','nominated_by'])
            ->paginate(12);

            $data = $data->toArray();
            $finaldata = $data['data'];
            unset($data['data']);
            $meta = array(
                'pagination' => array(
                    "current_page" => $data['current_page'], 
                    "total" => $data['total'], 
                    "per_page" => $data['per_page'], 
                    "links" => array(
                        "next" => $data['next_page_url']
                    ), 
                    "total_pages" => $data['last_page']
                )
            );
            return response()->json(['data'=>$finaldata,'meta'=>$meta,'message'=>'Data Listed successfully.', 'status'=>'success']);
        } catch (\Exception $e) {
            return response()->json(['data'=>[],'meta'=>[],'message'=>$e->getMessage(), 'status'=>'success']);
        }
    }

    
    public function sendNomination(Request $request)
    {

        $newname = '';
        if ($request->hasFile('attachments')) {
            $file = $request->file('attachments');
            $request->validate([
                'attachments' => 'file||mimes:jpeg,png,jpg,pdf',
            ]);
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $newname = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
            $destinationPath = public_path('uploaded/user_nomination_files/');
            $file->move($destinationPath, $newname);
        }
        $request->attachments = $newname;

        $teamNomination = UserNomination::USER_NOMINATION;
        if(isset($request->claim_award)){
            $teamNomination = UserNomination::CLAIM_NOMINATION;
        }


        /****** Campaign Setting ********/


        $setting_slug = $request->campaign_slug;
        $getCampaignData = $this->ripple_repository->getCampaignIDBySLug($setting_slug);
        $campaign_id = $getCampaignData->id;


        $result = $this->ripple_repository->getDataCampaignID($campaign_id);

        $points_allowed = $result->points_allowed;

        $approval_request = $result->approval_request_status;

        $budget_type = $result->budget_type;

        $level_1_approval = $result->level_1_approval;
        $level_2_approval = $result->level_2_approval;

        $inputPoint = $request->points;

        // Account IDs
        $receiverIds = explode(',', $request->user);
        $recevrCount = count($receiverIds);

        $senderUser = ProgramUsers::find($request->sender_id);
        


        // Get Sender program id using account_id

        $sender_account_id = $request->account_id; 
        $program_user_receiver = ProgramUsers::select('id')->where('account_id', $sender_account_id)->first();
        $sender_program_id = $program_user_receiver->id;


        /****** As per the Campaign Setting *****/


         if(!empty($receiverIds)){
            foreach ($receiverIds as $key => $receiverid_v) {

                $program_user_receiver = ProgramUsers::select('id')->where('account_id', $receiverid_v)->first();
                $receiverid = $program_user_receiver->id;
               
                $sendToUser = ProgramUsers::find($receiverid);
                    
                DB::beginTransaction();

                try {



                    /********************* If No Approval Required ***************************/

                    if( $approval_request == 0 && $points_allowed == 1){

                       // Waiting response from client

                    }

                    /********************* If Approval Required ***************************/
                  
                    if($approval_request == 1 && $points_allowed == 1){ 

                        $groupData = $this->ripple_repository->getLevel1Leads($receiverid); // 2 for L1 & 3 for L2


                        // Get lowest role of receiver
                        $groupId  = $groupData['user_group_id'];

                        
                        if($level_1_approval == 0){
                            $update_vale_l1 = 2;
                         }else{
                            $update_vale_l1 = 0;
                         }

                         if($level_2_approval == 0){
                            $update_vale_l2 = 2;
                         }else{
                            $update_vale_l2 = 0;
                         }

                        // Update User Nomination table so that L1 or L2 can approve
        
                        UserNomination::create([
                            'user'   => $sendToUser->account_id, // Receiver
                            'account_id' => $request->account_id, // Sender
                            'group_id' => $groupId,
                            'campaign_id' => $campaign_id,
                            'level_1_approval' => $update_vale_l1,
                            'level_2_approval' => $update_vale_l2,
                            'point_type' => $budget_type,
                            'reason' => $request->reason,
                            'value' => $request->value,
                            'points'  => $inputPoint,
                            'attachments' => $newname,
                            'team_nomination' => $teamNomination,
                        ]);

                        try {
                            DB::commit();
                        } catch (\Exception $e) {
                        
                        DB::rollBack();
                        return response()->json(['message'=>$e->getMessage(), 'status'=>'success']);
                        }
                        
                    }

                    return response()->json(['message'=>'Nomination has sent successfully.', 'status'=>'success']);
                 
                } catch (\Exception $e) {
                    
                    DB::rollBack();
                    $user_nomination = array();
                    return response()->json(['message'=> $e->getMessage(), 'status'=>'success']);

                }
            }  // end foreach
           
        } else {
             $user_nomination = array();
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }
        /*$user_nomination = $this->repository->create([
            'user' => $request->user,
            'account_id' => $request->account_id,
            'nomination_id' => $request->nomination_id,
            'reason' => "zxcxzc",//$request->reason,
            'value' => $request->value,
            'points' => $request->points,
            'attachments' => $newname,
            'team_nomination' => $teamNomination,
        ]);

        $approvals = $this->nomination_service->getApprovalAdmin($user_nomination);

        if( sizeof($approvals) > 0 )
        {
            $this->confirm_nomination($user_nomination, $approvals);
        }*/
        
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
        $user_nomination = $this->repository->find($id);

        return fractal($user_nomination, new UserNominationTransformer);
    }


    /**
     * Show the specified resource.
     *
     * @param $id
     *
     * @return Fractal
     */
    public function list_first_approvers($id): JsonResponse
    {
        $nomination = $this->nomination_repository->find($id);

        $approvals = $this->nomination_service->getFirstLevelApprovalUsers($nomination);

        return response()->json($approvals);

    }


    /**
     * @param $user_nomination
     * @param $approvals
     * @throws Exception
     */
    public function confirm_nomination($user_nomination, $approvals)
    {
        $sender = $user_nomination->account->name;
        $sender_email = $user_nomination->account->email;
//        $account_name = $user_nomination->account_name->name;
        $user = $user_nomination->user_relation->email;
        $user_name = $user_nomination->user_relation->first_name;
//        $value = $user_nomination->type->name;
        $value = $user_nomination->type->name;
//        $level = optional($user_nomination->level)->name; //todo understand where point is a foreign key
        $level = optional($user_nomination->level)->points; //todo understand where point is a foreign key
        $reason = $user_nomination->reason;

        // confirm nominator

        $subject ="Kafu by AD Ports - Nomination submitted ";

        $message ="Thank you for your nomination! We will inform you if the nomination is approved.";

        $this->nomination_service->sendmail($sender_email,$subject,$message);


        // send to approver

        // $nominated_by_group_name= $user_nomination->nominated_user_group_name;

        $subject = "Kafu by AD Ports - Nomination for approval";

        $link = "http://kafu.meritincentives.com/approvals/approve-level-1";

        $message = "Please approve {$user_name} nomination for the {$value} value which has been submitted by {$sender} for the following reason: {$reason} \n\r <br> \n\r <br>";

        $message .="Once approved {$user_name} will receive {$level} points to their account. \n\r <br> \n\r <br> ";


        //$message .= "Dear {$nominated_by_group_name}, please approve \n\r <br>";

        $message .= "<a href=".$link.">Click here to approve this nomination</a> <br>";

        $message .= "Please approve or decline only nomination for people reporting to you \n\r <br>";


        foreach ($approvals as $account)
        {
            $this->nomination_service->sendmail($account->email,$subject,$message);

            break;//only one receiver
        }
    }


    /**
     * @param $user_nomination
     * @param $approvals
     * @throws Exception
     */
    public function confirm_second_level($user_nomination, $approvals)
    {

        $sender=$user_nomination->account->name;
        $account_name=$user_nomination->account_name->name;
        $value=$user_nomination->type->name;
        $level=$user_nomination->level->name;
        $reason=$user_nomination->reason;
        $user_name = $user_nomination->user_relation->first_name;

        $subject="Kafu by AD Ports - Nomination submitted";

        $link = "http://kafu.meritincentives.com/approvals/approve-level-2";

        //$nominated_by_group_name= $user_nomination->nominated_user_group_name;

        $message = "Please approve {$user_name} nomination for the {$value} value which has been submitted by {$sender} for the following reason: {$reason} \n\r <br> \n\r <br>";

        $message .="Once approved {$user_name} will receive {$level} points to their account. \n\r <br> \n\r <br> ";

        //$message .= "Dear {$nominated_by_group_name}, please approve \n\r <br>";

        $message .= "<a href=".$link.">Click here to approve this nomination</a> <br>";

        $message .= "Please approve or decline only nomination for people reporting to you \n\r <br>";


        foreach ($approvals as $account)
        {
            $this->nomination_service->sendmail($account,$subject,$message);
            break;// only one reciver there
        }
    }



    /**
     *
     * Update the specified resource in storage.
     *
     * @param UserNominationRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(UserNominationRequest $request, $id): JsonResponse
    {

        $this->repository->update($request->all(), $id);

        return response()->json(['message' => 'Updated Successfully']);
    }


    public function updatePoints(Request $request, $id): JsonResponse
    {
        $nomination = $this->user_nomination_service->find($id);

        $this->repository->update(['points' => $request->points], $id);


        return response()->json(['Data Updated Successfully']);
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);

        return response()->json(['message' => 'Trashed Successfully']);
    }

public function updateLevelOne(Request $request, $id): JsonResponse
{
  
    DB::beginTransaction();
    try {
        $user_nomination = $this->user_nomination_service->find($id);
        $campaign_id = $user_nomination->campaign_id;
        $approval_status = $request->level_1_approval; // 1 for acceted and -1 decline


        // Get receiver program user id
        $receiver_account_id = $user_nomination->user; 
        $program_user_receiver = ProgramUsers::select('*')->where('account_id', $receiver_account_id)->first();
        $receiver_program_id = $program_user_receiver->id;

        // Get Sender program user id
        $program_user_sender = ProgramUsers::select('*')->where('account_id', $user_nomination->account_id)->first();
        $sender_program_id = $program_user_sender->id;
        
        // Points Need to add
        $points_update = $user_nomination->points; 

        $nominationData = [
            'level_1_approval' => $request->level_1_approval,
        ];

        $budget_type =  $user_nomination->point_type;

        if ($request->level_1_approval == -1 ) {

            $nominationData['reject_reason'] = $request->decline_reason;
            $nominationData['rajecter_account_id'] = $request->approver_account_id;

        } else {

            $get_campaign_setting = CampaignSettings::select('level_1_approval','level_2_approval')->where('campaign_id', $campaign_id)->first();

            $level1_v = $get_campaign_setting->level_1_approval;
            $level2_v = $get_campaign_setting->level_2_approval;

            if($level1_v == 1 && ($level2_v == 0 || $level2_v == 2 )){ 

            // L2 Approcal not required, points get creadited

            // Deduct from Level1 Budget

            

            // Get Sender program user id
            $approver_program_data = ProgramUsers::select('id')->where('account_id', $request->approver_account_id)->first();
            $approver_program_id = $approver_program_data->id;

            
            // Only for Nomination Type

            if($request->campaign_type == 4) {
          
                if($budget_type == 1){ 


                    // Campaign_Budget of current logged user

                   $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$approver_program_id)->where('campaign_id',$campaign_id)->latest()->first();
                   
                    if(!$campaign_budget){
                       
                        return response()->json(['message'=>"Budget is not allocated yet", 'status'=>'error']);

                    }else{

                        $campaign_budget_bal =  $campaign_budget->budget ? $campaign_budget->budget : 0;
                        
                        if($campaign_budget_bal < ($points_update)) {
                            return response()->json(['message'=>"You don't have enough balance to nominate", 'status'=>'error']);
                        }
                    }

                    // campaign Deduction

                    $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$approver_program_id)->where('campaign_id',$campaign_id)->latest()->first();
                    $campaign_budget_bal =  $campaign_budget->budget;

                    $currentBud = $campaign_budget_bal;
                    $finalBud = $currentBud-$points_update;

                    $updateSenderBudget = UserCampaignsBudget::where('program_user_id', $approver_program_id)->where('campaign_id',$campaign_id)->update([
                                'budget' => $finalBud,
                            ]);

                    // Logs
                    
                    $createRippleLog = UserCampaignsBudgetLogs::create([
                                    'program_user_id' => $approver_program_id,
                                    'campaign_id' => $campaign_id,
                                    'budget' => $points_update,
                                    'current_balance' => $campaign_budget_bal ? $campaign_budget_bal : 0,
                                    'description' => "deduction after approval",   
                                    'created_by_id' => $request->approver_account_id,     
                                ]);


                }else{

                    // Check current loged user Overall balance

                    $current_budget_bal = UsersPoint::select('balance')->where('user_id',$approver_program_id)->latest()->first();

                    $current_budget_bal = $current_budget_bal ? $current_budget_bal->balance : 0;
                    
                    if(!$current_budget_bal) {
                        //return response()->json(['message'=>"Overall Budget empty.", 'status'=>'error']);
                        return response()->json(['message'=>"Balance is not allocated", 'status'=>'error']);
                    }
                    if($current_budget_bal < ($points_update)) {
                        //return response()->json(['message'=>"Points should be less then or equal to budget points.", 'status'=>'error']);
                        return response()->json(['message'=>"You don't have enough overall balance to nominate", 'status'=>'error']);
                    }


                    // Overall balance deduction

                    $currentBud = UsersPoint::select('balance')->where('user_id',$approver_program_id)->latest()->first();
                    $currentBud = $currentBud ? $currentBud->balance : 0;
                    $finalPoints = $currentBud-$points_update;
                
                    $updateReciverBudget = UsersPoint::create([
                        'value'    => -$points_update, // +/- point
                        'user_id'    => $approver_program_id, // Approval program id
                        'transaction_type_id'    => 10,  // For Ripple
                        'description' => 'Deduction after approval',
                        'balance'    => $finalPoints, // After +/- final balnce
                        'created_by_id' =>$request->approver_account_id // Who send
                    ]);
                } 
                       
            } // Close Campiagn type condition




            //update receiver budget
            $currentBud = UsersPoint::select('balance')->where('user_id',$receiver_program_id)->latest()->first();
            
            $currentBud = $currentBud ? $currentBud->balance : 0;
            $finalPoints = $currentBud+$points_update;
            $updateReciverBudget = UsersPoint::create([
                'value'    => $points_update, // +/- point
                'user_id'    => $receiver_program_id, // Receiver
                'transaction_type_id'    => 10,  // For Ripple
                'description' => '',
                'balance'    => $finalPoints, // After +/- final balnce
                'created_by_id' => $sender_program_id // Who send
            ]);

            if($request->campaign_type == 4) {

                $sender_email = $user_nomination->account->email;
                $subject ="Kafu by AD Ports - Your nomination was approved !";
                $message = "Dear " . $user_nomination->account->name ;
                $message .="\n\r <br> Your nomination " . $user_nomination->nominated_account->name . " for the " . $user_nomination->project_name . " project has been approved ";
                $message .="\n\r <br> We encourage you to continue nominating your peers on Kafu, to help spread a positive and empowering culture in AD Ports. You may login and nominate by clicking <a href='https://kafu.meritincentives.com/wall-of-heros'>here</a>.";
                $this->nomination_service->sendmail($sender_email,$subject,$message);
            }



            if($request->campaign_type == 2) {

                /****** Start Send ecrad ***********/


                
                $image_url = [
                            'banner_img_url' => env('APP_URL')."/img/emailBanner.jpg",
                        ];

               $eCardDetails =  UsersEcards::select('users_ecards.new_image','users_ecards.image_path','users_ecards.image_message','ecards.card_title','ecards.card_image')
                ->leftJoin('ecards', 'ecards.id', '=', 'users_ecards.ecard_id')
                ->where(['users_ecards.id' => $user_nomination->ecard_id])
                ->get()->first();


                $new_img = $eCardDetails->image_path.$eCardDetails->new_image;
                $new_img_path = url($new_img);

                $data = [
                    'email' => $program_user_receiver->email,
                    'username' => $program_user_receiver->first_name.' '. $program_user_receiver->last_name,
                    'card_title' => $eCardDetails->card_title,
                    'sendername' => $program_user_sender->first_name.' '. $program_user_sender->last_name,
                    'image' => env('APP_URL')."/uploaded/e_card_images/".$eCardDetails->card_image,
                    'image_message' => $eCardDetails->image_message,
                    'color_code' => "#e6141a",
                    'link_to_ecard' => $new_img_path
                ];
                try {

                    Mail::send('emails.sendEcard', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
                        $m->to($data["email"])->subject($data["card_title"].' Ecard!');
                    });


                } catch (\Exception $e) {
                   
                   return response()->json(['message'=>$e->getMessage(), 'status'=>'error']);
                }

                /****** End Send ecrad ***********/
                }
            }


            $nominationData['reject_reason'] = $request->decline_reason;
            $nominationData['approver_account_id'] = $request->approver_account_id;
            $msgResponse ="Nomination has been approved successfully.";
        }

        
        
        if ($request->level_1_approval == -1 ) {


            if($request->campaign_type != 4) { // Ethank you refund as sender points gt deducted when card being sent but in nomination type, approval points get deducted so no refund in nomination

                // Revert Back the points

                if($budget_type == 1){ 

                    // campaign budget refund

                    $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$sender_program_id)->where('campaign_id',$campaign_id)->latest()->first();
                    $campaign_budget_bal =  $campaign_budget->budget;

                    $currentBud = $campaign_budget_bal;
                    $finalBud = $currentBud+$points_update;

                    $updateSenderBudget = UserCampaignsBudget::where('program_user_id', $sender_program_id)->where('campaign_id',$campaign_id)->update([
                    'budget' => $finalBud,
                    ]);

                    // Logs

                    $createRippleLog = UserCampaignsBudgetLogs::create([
                        'program_user_id' => $sender_program_id,
                        'campaign_id' => $campaign_id,
                        'budget' => $points_update,
                        'current_balance' => $campaign_budget_bal ? $campaign_budget_bal : 0,
                        'description' => "Budget refund by e thank you",   
                        'created_by_id' => $request->approver_account_id,     
                    ]);


                }else{

                    // Overall balance refund

                    $currentBud = UsersPoint::select('balance')->where('user_id',$sender_program_id)->latest()->first();
                    $currentBud = $currentBud ? $currentBud->balance : 0;
                    $finalPoints = $currentBud+$points_update;
                    $updateReciverBudget = UsersPoint::create([
                        'value'    => $points_update, // +/- point
                        'user_id'    => $sender_program_id, // Receiver
                        'transaction_type_id'    => 10,  // For Ripple
                        'description' => 'Refund as request declined',
                        'balance'    => $finalPoints, // After +/- final balnce
                        'created_by_id' => $request->approver_account_id // Who send
                    ]);
                }

            }


            //$user_nomination->account->email ="suruchi@visions.net.in";
            $sender_email = $user_nomination->account->email;
            $subject ="Kafu by AD Ports - Your nomination was declined !";
            $message = "Dear " . $user_nomination->account->name ;
            $message .="\n\r <br> Your nomination " . $user_nomination->nominated_account->name . " for the " . $user_nomination->project_name . " project has been declined for the following reason: " . $request->reason ." .";
            $message .="\n\r <br> We encourage you to continue nominating your peers on Kafu, to help spread a positive and empowering culture in AD Ports. You may login and nominate by clicking <a href='https://kafu.meritincentives.com/wall-of-heros'>here</a>.";


            $this->nomination_service->sendmail($sender_email,$subject,$message);
            $msgResponse ="Nomination has been declined successfully.";
           
        }

        $this->repository->update($nominationData, $id);
        DB::commit();
        return response()->json(['message'=>$msgResponse, 'status'=>'success']);

    }catch (\Exception $e) {
                       
        DB::rollBack();
        return response()->json(['message'=>$e->getMessage(), 'status'=>'error']);
    }


        
}



    public function testMail(): JsonResponse
    {
        $sender_email = "e.mahmoud124@gmail.com";
        $subject ="Kafu by AD Ports - Your nomination was approved!";
        $message ="Your nomination has been approved. Thank you for your contribution.";
        $this->nomination_service->sendmail($sender_email,$subject,$message);
        return response()->json(['Mail sent']);

    }


    public function updateLevelTwo(Request $request, $id): JsonResponse
    {

        DB::beginTransaction();

        try {

            $nominationData = [
                'level_2_approval' => $request->level_2_approval
            ];

            if ($request->level_2_approval == -1 ) {
                $nominationData['reject_reason'] = $request->decline_reason;
                $nominationData['rajecter_account_id'] = $request->approver_account_id;
            } else {
                $nominationData['reject_reason'] = $request->decline_reason;
                $nominationData['l2_approver_account_id'] = $request->approver_account_id;
            }
           
            // Nomination Data
            $user_nomination = $this->user_nomination_service->find($id);
            $campaign_id = $user_nomination->campaign_id;


            // Get receiver program user id
            $receiver_account_id = $user_nomination->user; 
            $program_user_receiver = ProgramUsers::select('*')->where('account_id', $receiver_account_id)->first();
            $receiver_program_id = $program_user_receiver->id;

            // Get Sender program user id
            $program_user_sender = ProgramUsers::select('*')->where('account_id', $user_nomination->account_id)->first();
            $sender_program_id = $program_user_sender->id;

            // Points Need to add
            $points_update = $user_nomination->points; 


             // Get Sender program user id
            $approver_program_data = ProgramUsers::select('*')->where('account_id', $request->approver_account_id)->first();
            $approver_program_id = $approver_program_data->id;

            $budget_type =  $user_nomination->point_type;

            // If request accepted

            if($request->level_2_approval == 1){


            // Only for Nomination Type

            if($request->campaign_type == 4) {

                if($budget_type == 1){ 


                    // Campaign_Budget of current logged user

                    $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$approver_program_id)->where('campaign_id',$campaign_id)->latest()->first();

                    if(!$campaign_budget){
                        
                        return response()->json(['message'=>'Budget is not allocated yet', 'status'=>'error']);
                        
                    }else{

                        $campaign_budget_bal =  $campaign_budget->budget ? $campaign_budget->budget : 0;
                        
                        if($campaign_budget_bal < ($points_update)) {
                            return response()->json(['message'=>"You don't have enough balance to nominate", 'status'=>'error']);
                        }
                    }

                    // campaign Deduction

                    $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$approver_program_id)->where('campaign_id',$campaign_id)->latest()->first();
                    $campaign_budget_bal =  $campaign_budget->budget;

                    $currentBud = $campaign_budget_bal;
                    $finalBud = $currentBud-$points_update;

                    $updateSenderBudget = UserCampaignsBudget::where('program_user_id', $approver_program_id)->where('campaign_id',$campaign_id)->update([
                                'budget' => $finalBud,
                            ]);

                    // Logs
                    
                    $createRippleLog = UserCampaignsBudgetLogs::create([
                                    'program_user_id' => $approver_program_id,
                                    'campaign_id' => $campaign_id,
                                    'budget' => $points_update,
                                    'current_balance' => $campaign_budget_bal ? $campaign_budget_bal : 0,
                                    'description' => "deduction after approval",   
                                    'created_by_id' => $request->approver_account_id,     
                                ]);


                }else{

                    // Check current loged user Overall balance

                    $current_budget_bal = UsersPoint::select('balance')->where('user_id',$approver_program_id)->latest()->first();

                    $current_budget_bal = $current_budget_bal ? $current_budget_bal->balance : 0;
                    
                    if(!$current_budget_bal) {
                        //return response()->json(['message'=>"Overall Budget empty.", 'status'=>'error']);
                        return response()->json(['message'=>'Budget is not allocated yet', 'status'=>'error']);
                    }
                    if($current_budget_bal < ($points_update)) {
                        //return response()->json(['message'=>"Points should be less then or equal to budget points.", 'status'=>'error']);
                        return response()->json(['message'=>"You don't have enough overall balance to nominate", 'status'=>'error']);
                    }


                    // Overall balance deduction

                    $currentBud = UsersPoint::select('balance')->where('user_id',$approver_program_id)->latest()->first();
                    $currentBud = $currentBud ? $currentBud->balance : 0;
                    $finalPoints = $currentBud-$points_update;
                
                    $updateReciverBudget = UsersPoint::create([
                        'value'    => -$points_update, // +/- point
                        'user_id'    => $approver_program_id, // Approval program id
                        'transaction_type_id'    => 10,  // For Ripple
                        'description' => 'Deduction after approval',
                        'balance'    => $finalPoints, // After +/- final balnce
                        'created_by_id' =>$request->approver_account_id // Who send
                    ]);
                } 
                       
            } // Close Campiagn type condition

           
            //update receiver budget
            $currentBud = UsersPoint::select('balance')->where('user_id',$receiver_program_id)->latest()->first();

            $currentBud = $currentBud ? $currentBud->balance : 0;
            $finalPoints = $currentBud+$points_update;
            $updateReciverBudget = UsersPoint::create([
                'value'    => $points_update, // +/- point
                'user_id'    => $receiver_program_id, // Receiver
                'transaction_type_id'    => 10,  // For Ripple
                'description' => '',
                'balance'    => $finalPoints, // After +/- final balnce
                'created_by_id' => $sender_program_id // Who send
            ]);
            

             if($request->campaign_type == 2) {


                /****** Start Send ecrad ***********/
                
                $image_url = [
                                'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
                                'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
                                'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
                                'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
                            ];

               $eCardDetails =  UsersEcards::select('users_ecards.image_message','ecards.card_title','ecards.card_image')
                ->leftJoin('ecards', 'ecards.id', '=', 'users_ecards.ecard_id')
                ->where(['users_ecards.id' => $user_nomination->ecard_id])
                ->get()->first();


                $data = [
                    'email' => $program_user_receiver->email,
                    'username' => $program_user_receiver->first_name.' '. $program_user_receiver->last_name,
                    'card_title' => $eCardDetails->card_title,
                    'sendername' => $program_user_sender->first_name.' '. $program_user_sender->last_name,
                    'image' => env('APP_URL')."/uploaded/e_card_images/".$eCardDetails->card_image,
                    'image_message' => $eCardDetails->image_message,
                    'color_code' => "#e6141a",
                ];
                try {

                    Mail::send('emails.sendEcard', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
                        $m->to($data["email"])->subject($data["card_title"].' Ecard!');
                    });

                } catch (\Exception $e) {
                   
                   return response()->json(['message'=> $e->getMessage(), 'status'=>'error']);
                }

                /****** End Send ecrad ***********/


             }else{
            

                 /*   if($user_nomination->team_nomination == 1){
                        $points = $user_nomination->points;
                    }else{
                        // $points = optional($user_nomination->level)->points ?? $user_nomination->value;// $user_nomination->level->points;
                        $points = $user_nomination->points;
                    }
                    $data['value']       = $points;
                    $data['description'] = '';
                    $data['user_nominations_id'] = $id;
                    $data['created_by_id'] = $request->approver_account_id;
                    $user = ProgramUsers::where('account_id',$user_nomination->user)->first(); // todo remind omda & mahmoud this is error

                    // there is no error ya kenany
                    $this->point_service->store($user, $data);*/

                    // confirm nominator that nomination approve
                    $sender_email = $user_nomination->account->email;
                    $subject ="Kafu by AD Ports - Your nomination was approved!";
                    $message ="Your nomination has been approved. Thank you for your contribution.";
                    $this->nomination_service->sendmail($sender_email,$subject,$message);

                    $sender_email = $user_nomination->user_relation->email;
                    $subject ="Kafu by AD Ports - Congratulations!";
                    $message ="Congratulations! You have been nominated. \n\r <br> Please check Kafu wall of heroes to see who nominated you ";
                    $message .="<a href='https://kafu.meritincentives.com/wall-of-heros'>Click here to check your nomination</a> ";

                    $this->nomination_service->sendmail($sender_email,$subject,$message);
                    if($user_nomination->team_nomination != 1){
                        /*AccountBadges::create([
                            'account_id' => $user_nomination->account_id, //todo account for nominated user
                            'nomination_type_id' => $user_nomination->value
                        ]);*/
                    }

                }
            } else if ($request->level_2_approval == -1 ) {
                 
                 // Revert Back the points

                if($request->campaign_type != 4) { // Ethank you refund as sender points gt deducted when card being sent but in nomination type, approval points get deducted so no refund in nomination
                    if($budget_type == 1){ 


                        // campaign budget refund

                        $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$sender_program_id)->where('campaign_id',$campaign_id)->latest()->first();
                        $campaign_budget_bal =  $campaign_budget->budget;

                        $currentBud = $campaign_budget_bal;
                        $finalBud = $currentBud+$points_update;

                        $updateSenderBudget = UserCampaignsBudget::where('program_user_id', $sender_program_id)->where('campaign_id',$campaign_id)->update([
                                    'budget' => $finalBud,
                                ]);

                        // Logs
                        
                        $createRippleLog = UserCampaignsBudgetLogs::create([
                                        'program_user_id' => $sender_program_id,
                                        'campaign_id' => $campaign_id,
                                        'budget' => $points_update,
                                        'current_balance' => $campaign_budget_bal ? $campaign_budget_balt : 0,
                                        'description' => "Budget refund by e thank you",   
                                        'created_by_id' => $request->approver_account_id,     
                                    ]);


                    }else{
                    // Overall balance refund

                         $currentBud = UsersPoint::select('balance')->where('user_id',$sender_program_id)->latest()->first();
                                $currentBud = $currentBud ? $currentBud->balance : 0;
                                $finalPoints = $currentBud+$points_update;
                                $updateReciverBudget = UsersPoint::create([
                                    'value'    => $points_update, // +/- point
                                    'user_id'    => $sender_program_id, // Receiver
                                    'transaction_type_id'    => 10,  // For Ripple
                                    'description' => 'Refund as request declined',
                                    'balance'    => $finalPoints, // After +/- final balnce
                                    'created_by_id' =>$request->approver_account_id // Who send
                                ]);
                    }

                }
                $sender_email = $user_nomination->account->email;
                $subject ="Kafu by AD Ports - Your nomination was declined !";
                $message = "Dear " . $user_nomination->account->name ;
                $message .="\n\r <br> Your nomination " . $user_nomination->nominated_account->name . " for the " . $user_nomination->project_name . " project has been declined for the following reason: " . $request->reason ." .";
                $message .="\n\r <br> We encourage you to continue nominating your peers on Kafu, to help spread a positive and empowering culture in AD Ports. You may login and nominate by clicking <a href='https://kafu.meritincentives.com/wall-of-heros'>here</a>.";
                $this->nomination_service->sendmail($sender_email,$subject,$message);
            }

            $this->repository->update($nominationData, $id);
            DB::commit();
            return response()->json(['message'=> "Nomination has been approved successfully.", 'status'=>'success']);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message'=> $th->getMessage(), 'status'=>'success']);

        }

    }

    /**
     * @param $nomination_id
     * @param Account $account_id
     * @return Fractal
     */
    public function getUsersBy($nomination_id, Account $account_id) {
        
        $logged_user_id = $account_id->id;
        $user_group_data =  DB::table('users_group_list')
        ->where('account_id', $logged_user_id)
        ->where('status', '1')
        ->where('user_role_id', '2') // 2 for Level1
        ->get()->toArray(); 


        /*$loggedProgramUserData =  ProgramUsers::select('id')->where('account_id',$logged_user_id)->first();

        $loggedProgramUserId = $loggedProgramUserData->id;*/

        foreach ($user_group_data as $key => $value) {
            $groupid[$key] = $value->user_group_id;
        }
       
        $approved = UserNomination::where([
           // 'campaign_id' => $nomination_id,
            'level_1_approval' => 0,
        ])
            ->whereIn('group_id', $groupid)
            ->where('account_id', '!=' , $logged_user_id)
            ->orderBY('id','desc')
            ->paginate(12);

        //get the user group
        //$role_name = $account_id->getRoleNames()[0];

        // get nomination for the department
        // $approved = $approved->filter(function ($approve) use ($role_name, $account_id){
        //     return $approve->nominated_account->def_dept_id >  0;
        // })->values();

        return fractal($approved, new UserNominationTransformer());
    }

    /**
     * @param $nomination_id
     * @param Account $account_id
     * @return Fractal
     */
    public function getApprovedUsersLevelOne($nomination_id, Account $account_id)
    {
        $approved = UserNomination::where(['level_1_approval' => 1, 'nomination_id' => $nomination_id])->orderBY('id','desc')
            ->get();

        //get the user group
        $role_name = $account_id->getRoleNames()[0];
        // map the collection where has that role
        $approved = $approved->filter(function ($approve) use ($role_name){
            return $approve->user_relation->account->hasRole($role_name);
        })->values();

        return fractal($approved, new UserNominationTransformer());
    }


    /**
     * @param $nomination_id
     * @param Account $account_id
     * @return Fractal
     */
    public function getApprovedUsersLevelTwo($nomination_id, Account $account_id)
    {
        $approved = UserNomination::where(['level_1_approval' => 1, 'level_2_approval' => 1, 'nomination_id' => $nomination_id])->orderBY('id','desc')
            ->get();

        //get the user group
        $role_name = $account_id->getRoleNames()[0];
        // map the collection where has that role
        $approved = $approved->filter(function ($approve) use ($role_name){
            return $approve->user_relation->account->hasRole($role_name);
        });


        return fractal($approved, new UserNominationTransformer());

    }

    /**
     * @return ReportExports
     */
    public function report()
    {

        $done_level_one_nomination = collect();
        $done_level_two_nomination = collect();

        $nominations = Nomination::all();

        foreach ($nominations as $nomination){
            if ($nomination->approval_level == 'approval_level_1')
                $done_level_one_nomination->push(UserNomination::where(['level_1_approval' => 1, 'nomination_id' => $nomination->id])->get());
            else
                $done_level_two_nomination->push(UserNomination::where(['level_1_approval' => 1, 'level_2_approval' => 1, 'nomination_id' => $nomination->id])->get());
        }


        $result =  array_merge($done_level_one_nomination->toArray(), $done_level_two_nomination->toArray());

        $result =  array_merge($result[0], $result[1]);

        $user_nomination = collect();

        foreach ($result as $record){
            $user_nomination->push(new UserNomination($record));
        }

        header("Content-Type: application/csv");
        header("Content-Description: attachment;Filename=report.csv");

        $header = [
            '#',
            'Campaign name',
            'Nominated user',
            'Nominated by',
            'Nominated at',
            'Nomination time'
        ];

        $header = array_map('utf8_decode', $header);

        $file = fopen('report.csv', 'w');

        fputcsv($file, $header, ';');


        foreach ($user_nomination as $value){
            fputcsv($file, [
                $value->id,
                $value->campaign->name,
                $value->nominated_account->email,
                $value->account->email,
                $value->created_at->diffForHumans(),
                $value->created_at,
            ], ';');
        }


        return response()->json('Data created successfully link: '.url('/report.csv'), 200);

    }


    /**
     * @return JsonResponse
     */
    public function reportToGetAllNominationDetails(): JsonResponse
    {

        $user_nomination = UserNomination::all();

        header('Content-Type: application/csv');
        header('Content-Description: attachment;Filename=report.csv');

        $header = [
            '#',
            'Nominee',
            'Reason for nomination ',
            'value of nomination',
            'nomination points',
            'Approver',
            'Approval status',
            'nominator',
            'nomination date',
            'approval date',
        ];

        $header = array_map('utf8_decode', $header);

        $file = fopen('nomination_reports/allNominationReport-'. date('Y-m-d') .'-.csv', 'w');

        fputcsv($file, $header, ';');

        foreach ($user_nomination as $value){
            if ($value->level_1_approval === 1 && $value->level_2_approval === 0):
                $approval_status = 'Approved Level One';
           elseif ($value->level_1_approval === 1 && $value->level_2_approval === 1):
               $approval_status = 'Approved Level Two';
            elseif($value->level_1_approval === 0 && $value->level_2_approval === 0):
                $approval_status = 'Not Approved yet';
            else:
                $approval_status = 'Declined';
            endif;

            fputcsv($file, [
                $value->id,
                $value->nominated_account->email,
                $value->reason,
                $value->type  ? optional($value->type->valueset)->name : '-',
                optional($value->level)->points,
                $value->nominated_account->getRoleNames(),
                $approval_status,
                $value->account->email,
                $value->created_at->diffForHumans(),
                $value->updated_at->diffForHumans(),
            ], ';');
        }

        return response()->json('Data created successfully link: '.url('nomination_reports/allNominationReport-'.date('Y-m-d').'-.csv'), 200);

    }

    /**
     * @param TeamNominationRequest $request
     * @return Fractal
     * @throws Exception
     */
    public function teamNomination(TeamNominationRequest $request)
    {
        $message = 'Invalid Access Token!';
        $status = false;
        $vpaccount = \Auth::user();
        $vpdpt =  $vpaccount->def_dept_id;

        $newname = '';
        $destinationPath = public_path('uploaded/user_nomination_files/');

        if ($request->hasFile('nomination_file')) {
            $file = $request->file('nomination_file');
            $request->validate([
                'nomination_file' => 'required|file||mimes:doc,docx,csv,xlsx,xls,txt,pdf',
            ]);
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $newname = 'team_nomination_'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
            $file->move($destinationPath, $newname);
        }

        $users = json_decode($request->get('users'), true);
        $data = [
            'nomination_id'     =>  $request->get('nomination_id'),
            'account_id'        =>  $vpaccount->id,//$loggedin_user->id,//$request->get('account_id'),
            'project_name'      =>  $request->get('project_name'),
            'reason'            =>  $request->get('reason'),
            'level_1_approval'  =>  0,
            'level_2_approval'  =>  0,
            'team_nomination'   =>  UserNomination::TEAM_NOMINATION,
            'attachments'        => ($newname!='')?$newname:'',
        ];

        foreach ($users as $key => $value) {
            $useracc = $this->account_service->show($value['accountid']);
            if( $vpaccount->def_dept_id == $useracc->def_dept_id ) {
                $data['points'] = $value['value'];
                $data['value'] = $value['value'];
                $data['user'] = $value['accountid'];
                $user_nomination = $this->repository->create($data);
                    //if(!empty($user_nomination))
                    //$user_nomination->sendEmail($this->nomination_service); // NO need individual notification on request
            }
        }
        $status = true;

        // $sender_email = 'narinder@visions.net.in';//$user_nomination->account->email;

        // $subject ="Kafu by AD Ports - New project nomination!";
        // $message = "Dear " . "VP of HR" .  "\n\r <br>";
        // $a = count($users);
        // $b = $user_nomination->project_name;
        // $c =  $user_nomination->account->name;
        // $d = $user_nomination->account->defaultDepartment->name;
        // $message .="There are " . count($users) . "  nomination(s) for the " . $user_nomination->project_name . " project which has been submitted by " . $user_nomination->account->name . " - VP of " . $user_nomination->account->defaultDepartment->name . ", for the following reason: " . $user_nomination->reason . ".";
        // $message .= "\n\r <br> Once approved  each nominee will receive " . $user_nomination->value . " points to their account.";
        // $message .= "\n\r <br> *Click <a href='https://kafu.meritincentives.com/NominationApprove'>here</a> to review and approve this nomination*";

        // $this->nomination_service->sendmail($sender_email,$subject,$message);

        $message = 'Team Nominations Submitted Successfully.';

        return response()->json(['message'=>$message,'status'=>$status]);
    }

    /**
     * @param $nomination_id
     * @param Account $account_id
     * @return Fractal
     */
    public function pendingApprovals($nomination_id, Request $request)
    {

            $status = $request->get('status');
            $search = $request->get('keyword');

            $useraccount = \Auth::user();
            $userdpt =  $useraccount->def_dept_id;

            $approved = null;

            //if  ($useraccount->hasRole('ADPortHR')){

                            $approved = UserNomination::where([
                                'nomination_id' => $nomination_id,
                                'level_1_approval'  => $status,
                                'level_2_approval'  => 0,
                             //   'team_nomination'   => UserNomination::TEAM_NOMINATION,
                            ])
                                ->orderBY('id','desc')->paginate(10);

            // } else if($useraccount->hasRole('ADPortVP')){

            //     $approved = UserNomination::join('accounts', 'user_nominations.user', '=', 'accounts.id')
            //     ->where('accounts.def_dept_id', '=', $userdpt)
            //     ->where([
            //         'nomination_id' => $nomination_id,
            //         'level_1_approval'  => $status,
            //         'level_2_approval'  => 0,
            //      //   'team_nomination'   => UserNomination::TEAM_NOMINATION,
            //     ])
            //         ->select('accounts.id AS acc_id', 'user_nominations.*')
            //         ->orderBY('user_nominations.id','desc')->paginate(10);
            // }


            $response = fractal($approved, new TeamNominationTransformer())->toArray();
            $response['status'] = true;
            $response['message'] = 'Request Successfull';

            //get the user group
            // $role_name = $account_id->getRoleNames()[0];
            // // map the collection where has that role
            // $approved = $approved->filter(function ($approve) use ($role_name){
            //     return $approve->user_relation->account->hasRole($role_name);
            // })->values();

        return $response;
    }

    public function approvedNomination(UpdateTeamNominationRequest $request): JsonResponse
    {

        $useraccount = \Auth::user();
        $userdpt =  $useraccount->def_dept_id;

        if  ($useraccount->hasRole('ADPortHR')){

            $id = $request->userNiminationId;
            $user_nomination = $this->user_nomination_service->find($id);
            $data['value']   = $user_nomination->points;
            $data['description'] = $user_nomination->project_name;

            if($user_nomination->level_1_approval == 0 ){ // only new nomonation can be approve

                $this->repository->update(['level_1_approval' => 1], $id);

                $user = ProgramUsers::where('account_id',$user_nomination->user)->first(); // todo remind omda & mahmoud this is error

                $this->point_service->store($user, $data);

                // confirm nominator that nomination approve

                $sender_email = $user_nomination->account->email;

                $subject ="Kafu by AD Ports - Your nomination was approved!";
                $message = "Dear " . $user_nomination->account->name .  "\n\r <br>";

                $message .="Your nomination  for the " . $user_nomination->project_name . " project has been successfully approved! As a result, " . $user_nomination->nominated_account->name . " has been successfully awarded with " . $user_nomination->value  . " to their Kafu account.";

                $message .="\n\r <br> To view this award on the Kafu wall of fame, please Click  <a href='https://kafu.meritincentives.com/wall-of-heros'>here</a>.";


                $this->nomination_service->sendmail($sender_email,$subject,$message);


                // congratulate user that nomination approve

                $sender_email = $user_nomination->nominated_account->email;

                $subject ="Kafu by AD Ports - Congratulations!";
                $message = "Dear " . $user_nomination->nominated_account->name ;
                $message .="\n\r <br> Congratulations! \n\r <br> Your diligence and dedication towards the " . $user_nomination->project_name . " project, have played a tremendous role towards its success!";
                $message .= "\n\r <br> As a sign of gratitude, you have been awarded with " . $user_nomination->value  . " to your Kafu account.";
                $message .= "\n\r <br>  *Click <a href='https://kafu.meritincentives.com/wall-of-heros'>here</a> to view more details on why you have been awarded, and <a href='https://kafu.meritincentives.com/rewards'>here</a>  to spend your points towards an exciting catalogue of rewards!*";
                $message .=" ";

                $this->nomination_service->sendmail($sender_email,$subject,$message);

                return response()->json(['Prroved Successfully']);
            }
        }

        return response()->json(['Error! Only VP of HR can approve.']);

    }

    public function rejectNomination(RejectTeamNominationRequest $request): JsonResponse
    {
        $useraccount = \Auth::user();
        $userdpt =  $useraccount->def_dept_id;

        if  ($useraccount->hasRole('ADPortHR')){

                $id = $request->userNiminationId;

                $user_nomination = $this->user_nomination_service->find($id);


                if($user_nomination->level_1_approval == 0 ){// only new nomonation can be rejected

                $this->repository->update(['level_1_approval' => -1,'reject_reason'=>$request->reason], $id);

                $user = ProgramUsers::where('account_id',$user_nomination->user)->first(); // todo remind omda & mahmoud this is error

                // confirm nominator that nomination approve

                $sender_email = $user_nomination->account->email;

                $subject ="Kafu by AD Ports - Your nomination was declined !";
                $message = "Dear " . $user_nomination->account->name ;
                $message .="\n\r <br> Your nomination " . $user_nomination->nominated_account->name . " for the " . $user_nomination->project_name . " project has been declined for the following reason: " . $request->reason ." .";
                $message .="\n\r <br> We encourage you to continue nominating your peers on Kafu, to help spread a positive and empowering culture in AD Ports. You may login and nominate by clicking <a href='https://kafu.meritincentives.com/wall-of-heros'>here</a>.";
                //$message .="To view this award on the Kafu wall of fame, please <a href='https://kafu.meritincentives.com/wall-of-heros'>Click here</a>.";

                $this->nomination_service->sendmail($sender_email,$subject,$message);

                return response()->json(['Rejection Complateted.']);
            }
        }

        return response()->json(['Error! Only VP of HR can Reject.']);
    }

    /**
     * Export report Route action method for exporting csv
     * @param NominationReportExportRequest $request
     * @return mixed
     */
    public function exportReport(NominationReportExportRequest $request)
    {
        return Excel::download(new NominationReportExport($request->get('status', null), $request), 'nominations.csv');
    }

    /**
     * @param GetRequest $request
     * @return AnonymousResourceCollection
     */
    public function nominations(GetRequest $request)
    {
        return UserNominationResource::collection($this->repository->getWithDateRange($request));
    }

    /**
     * @param GetRequest $request
     * @return AnonymousResourceCollection
     */
    public function getClaimTypes()
    {
        $claim_type = $this->nomination_repository->getClaimType();
        return fractal($claim_type, new ClaimTypeTransformer);
    }

    /**
     * @param TeamNominationRequest $request
     * @return Fractal
     * @throws Exception
     */
    public function addClaim(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'claim_type_id' => 'required',
        ]);

        $claim_type_id = $request->input('claim_type_id');
        $user_id = $request->input('user_id');
        $reason = $request->input('description');
        $newname = '';

        if ($request->hasFile('claim_file')) {
            $file = $request->file('claim_file');
            $request->validate([
                'claim_file' => 'file||mimes:jpeg,png,jpg,pdf',
            ]);
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $newname = 'EN'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
            $destinationPath = public_path('uploaded/user_claim_files/');
            $file->move($destinationPath, $newname);
        }
        UserClaim::updateOrCreate([
            'user_id' => $user_id,
            'claim_type_id' => $claim_type_id,
            'reason' => $reason,
            'attachment_path' => $newname,
        ]);

        return response()->json(['message'=>'Your Claim required has been submitted successfully.','status'=>200]);
    }

    public function getUserClaims($user_id, Request $request){
        $userClaimList = UserClaim::where('approval_status', 0)->where('user_id', '!=' , $user_id)->get()->all();
        return fractal($userClaimList, new UserClaimTransformer);
    }

    public function approveClaim(Request $request){
       $request->validate([
            'claim_id' => 'required',
            'approval_by' => 'required',
            'points' => 'required',
        ]);

        $userClaim = UserClaim::find($request->claim_id);

        $claim_user_id = $userClaim->user_id;

        $userClaim->approval_decline_reason = $request->approval_reason;
        $userClaim->approval_status = 1;
        $userClaim->approval_by = $request->approval_by;
        $userClaim->save();

        $data['value']       = $request->points;
        $data['description'] = '';
        $data['created_by_id'] = $request->approval_by;
        $user = ProgramUsers::where('id',$claim_user_id)->first(); // todo remind omda & mahmoud this is error

        $this->point_service->store($user, $data);
        return response()->json(['message'=>'Claim approved successfully.','status'=>200]);
    }

    public function declineClaim(Request $request){
        $request->validate([
             'claim_id' => 'required',
             'decline_by' => 'required',
         ]);

         $userClaim = UserClaim::find($request->claim_id);

         $userClaim->approval_decline_reason = $request->decline_reason;
         $userClaim->approval_status = -1;
         $userClaim->approval_by = $request->decline_by;
         $userClaim->save();
         return response()->json(['message'=>'Claim declined successfully.','status'=>200]);
     }

    public function getL2NominatinsList($nomination_id, Account $account_id) {

        $texto='';
        $logged_user_id = $account_id->id;
        $user_group_data =  DB::table('users_group_list')
        ->where('account_id', $logged_user_id)
        ->where('status', '1')
        ->where('user_role_id', '3') // 3 for Level2
        ->get()->toArray(); 
   
        foreach ($user_group_data as $key => $value) {
            $groupid[$key] = $value->user_group_id;
        }

        if(!empty($groupid)){

            $approved = UserNomination::where([
            'level_2_approval' => 0,
            ])
            ->whereIn('group_id', $groupid)
            ->where(function ($query) use ($texto){

                $query->where('level_1_approval', '1')
                ->orWhere('level_1_approval', '2');
            })
            ->orderBY('id','desc')
            ->paginate(12);

        }else{
            $approved = array();
        }

        //get the user group
       // $role_name = $account_id->getRoleNames()[0];

        // get nomination for the department
        // $approved = $approved->filter(function ($approve) use ($role_name, $account_id){
        //     return $approve->nominated_account->def_dept_id >  0;
        // })->values();
        return fractal($approved, new UserNominationTransformer());
    }
}
