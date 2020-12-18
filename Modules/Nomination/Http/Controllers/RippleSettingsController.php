<?php

namespace Modules\Nomination\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Fractal\Fractal;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\CampaignTypes;
use Modules\Nomination\Models\CampaignSettings;
use Modules\Program\Models\Ecards;
use Modules\Nomination\Transformers\RippleSettingsTransformer;
use Modules\Nomination\Repositories\RippleSettingsRepository;
use Modules\User\Models\ProgramUsers;
use Modules\User\Models\RippleBudgetLog;
use Modules\User\Models\UsersPoint;
use Modules\Program\Models\UsersEcards;
use Modules\Nomination\Models\UserRipples; 
use Modules\Nomination\Models\UserNomination;
use Modules\User\Models\UserCampaignsBudget;
use Modules\User\Models\UserCampaignsBudgetLogs;
use DB;
use Illuminate\Support\Facades\Mail;
use File;

class RippleSettingsController extends Controller
{


    private $repository;

    public function __construct(RippleSettingsRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * API: Save Ripple setting as per the Admin input
     * 
     */

    public function saveRippleSettings(Request $request)
    {
        try {

            $data = $request->all();
            $campaign_id = $data['campaign_id'];

            $rules = [
                'campaign_id' => 'required|integer|exists:value_sets,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $page_slug = $request->name;
            $delimiter = '_';

            $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $page_slug))))), $delimiter));
           
    
            $campainData = $this->repository->getCampaignNameById($request->name, $campaign_id);

            if(count($campainData) > 0){

                return response()->json(['message' => 'Campaign name is already used.', 'errors' => $validator->errors()], 422);

            }


            $nameCheck = ValueSet::where('id', $campaign_id)->update(['name' => $request->name,'status' => $request->status, 'campaign_slug' => $slug ]);
            
        
            // Check If campaign setting are there or not
            if (CampaignSettings::where('campaign_id', '=', $campaign_id)->count() > 0) {
                // Update
                $rules = [
                    'campaign_id' => 'required|integer|exists:campaign_settings,campaign_id',
                ];

                $validator = \Validator::make($request->all(), $rules);

                 if ($validator->fails())
                        return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
                
                 CampaignSettings::where('campaign_id', $campaign_id)->update([

                    'send_multiple_status' => $request->send_multiple_status,
                    'approval_request_status' => $request->approval_request_status,
                    'level_1_approval' => $request->level_1_approval,
                    'level_2_approval' => $request->level_2_approval,
                    'budget_type' => $request->budget_type,
                    'min_point' => $request->min_point,
                    'max_point' => $request->max_point,
                    'points_allowed' => $request->points_allowed,
                 ]);

            }else{
                // Create
                $campain_setting = CampaignSettings::create([
                    'campaign_id' => $request->campaign_id,
                    'send_multiple_status' => $request->send_multiple_status,
                    'approval_request_status' => $request->approval_request_status,
                    'level_1_approval' => $request->level_1_approval,
                    'level_2_approval' => $request->level_2_approval,
                    'budget_type' => $request->budget_type,
                    'points_allowed' => $request->points_allowed,
                ]);
           
            }
            return response()->json(['message' => 'Settings has been updated successfully.'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        }
    }


    /**
     * API: Ecard Save Handler
     * 
     */

    public function createEcardsRipple(Request $request) {
        try {

            $rules = [
                'card_title' => 'required|unique:ecards,card_title',
                'image'   => 'required'
            ];
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $imgName = '';
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $imgName = 'ripple_e_card'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $destinationPath = public_path('uploaded/e_card_images/');
                $file->move($destinationPath, $imgName);
            }
            $newCard = Ecards::create([
                'card_title' => $request->card_title,
                'card_image' => $imgName,
                'campaign_id' => $request->campaign_id,
                'allow_points' => $request->points_allowed,
                'status' => '1'
            ]);

            return response()->json(['status' => true, 'message' => 'E-card has been created successfully.', 'data' => $newCard]);

        } catch (\Throwable $th) {
            return response()->json(['status' => true, 'message'=>'Something went wrong! Please try after some time.']);
        }
    }

    /**
     * API - Update : Ecard Save Handler
     * 
    */

     public function updateEcardsRipple(Request $request) {

        try {

            $rules = [
                'id' => 'required|integer|exists:ecards,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $file_name = $file->getClientOriginalName();
                $file_ext = $file->getClientOriginalExtension();
                $fileInfo = pathinfo($file_name);
                $filename = $fileInfo['filename'];
                $imgName = 'ripple_e_card'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $destinationPath = public_path('uploaded/e_card_images/');
                $file->move($destinationPath, $imgName);
            }else{
                $imgName = $request->img_name;
            }
            
            $ecardId = $request->id;
            
            Ecards::where('id', $ecardId)->update([
                'card_title' => $request->card_title,
                'card_image' => $imgName,
                'allow_points' => $request->points_allowed,
            ]);

            return response()->json(['status' => true, 'message' => 'E-card has been updated successfully.']);

        } catch (\Throwable $th) {
            return response()->json(['status' => true, 'message'=>'Something went wrong! Please try after some time.']);
        }
    }

    /**
     * API - Get Ripple Settings
     * 
    */

    public function getRippleSettings($id): Fractal
    {
        $result = $this->repository->getRippleSettingsBy($id);
        return fractal($result, new RippleSettingsTransformer);
    }

    /**
     * API - Get Ripple Settings
     * 
    */

    public function getRippleSettingsBySlug($slug): Fractal
    {

        $getCampaignData = $this->repository->getCampaignIDBySLug($slug);
        $campaign_id = $getCampaignData->id;

        $result = $this->repository->getRippleSettingsBy($campaign_id);
        return fractal($result, new RippleSettingsTransformer);
    }

    /**
     * API - E-card Template Active/Inactive
     * 
    */


     public function ecardStatusChange(Request $request) {
        try {

            $rules = [
                'id' => 'required|integer|exists:ecards,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $ecardId = $request->id;
            Ecards::where('id', $ecardId)->update([
                'status' => $request->status,

            ]);

        
            return response()->json(['message' => 'Status has been changed successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }

    /**
     * API - E-card Template Delete  // Not in use
     * 
    */


     public function ecardStatusDelete(Request $request) {
        try {
            $rules = [
                'id' => 'required|integer|exists:ecards,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $ecardId = $request->id;
            $affectedRows = Ecards::where('id', '=', $ecardId)->delete();

        
            return response()->json(['message' => 'Status has been changed successfully.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
    }


    /**
     * API - Get Ripple Budget  // Not in use
     * 
    */


    public function rippleBudgetByEmail(Request $request) {


        try {
            //$email_address=  $request->email_add;
            $budget_bal = $this->repository->getRippleBudget($request);

            return response()->json(['data' => $budget_bal], 200);
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }

    }

    /**
     * API - Front End : Send E-card
     * 
    */

    public function sendEcardRipple(Request $request)
    {

        
        $data = array();

        $rules = [
            'sender_id' => 'required|integer',
            'image_message' => 'required',
            'ecard_id' => 'required',
            'send_type' => 'required',
            'send_to_id' => 'required',
            'campaign_slug' => 'required'
        ];
        $validator = \Validator::make($request->all(), $rules);

        $userRoleId  = 3; // Logged user Role ID from : user_roles table
        $userGroupId = 1; // User Selected group


        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $setting_slug = $request->campaign_slug;
        $getCampaignData = $this->repository->getCampaignIDBySLug($setting_slug);
        $campaign_id = $getCampaignData->id;
        

        // Get Campaign Setting Data by Campaign id

        // Sender id is Program user id

        $result = $this->repository->getDataCampaignID($campaign_id);
        $approval_request = $result->approval_request_status;
        $budget_type = $result->budget_type;
        $level_1_approval = $result->level_1_approval;
        $level_2_approval = $result->level_2_approval;
        $inputPoint = $request->points;
        $receiverIds = explode(',', $request->send_to_id);
        $recevrCount = count($receiverIds);
        $senderUser = ProgramUsers::find($request->sender_id);

        //$senderUser =  ProgramUsers::where('account_id', $request->sender_id)->first();
        $points_allowed = $result->points_allowed;

        $failed = [];

        // CHECK IF HAS BALANCE OR NOT

        if($budget_type == 1 && $points_allowed == 1) {     // for ripple budget

             // Campaign_Budget
            $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$request->sender_id)->where('campaign_id',$campaign_id)->latest()->first();

            if(!$campaign_budget){

                return response()->json(['message' => 'Budget is not allocated yet', 'errors' => $validator->errors()], 422);

            }else{
                 $currentBud =  $campaign_budget->budget ? $campaign_budget->budget : 0;
            }

            if(!$currentBud) {
                return response()->json(['message'=>"Budget is not allocated yet.", 'status'=>'error']);
            }
            if($currentBud < ($inputPoint*$recevrCount)) {
                return response()->json(['message'=>"Insufficient campaign budget", 'status'=>'error']);
            }
        } else { 

            if($points_allowed == 1){
                // for overall budget
                $current_budget_bal = UsersPoint::select('balance')->where('user_id',$request->sender_id)->latest()->first();
                $current_budget_bal = $current_budget_bal ? $current_budget_bal->balance : 0;
                if(!$current_budget_bal) {
                    return response()->json(['message'=>"Balance is not allocated yet", 'status'=>'error']);
                }
                if($current_budget_bal < ($inputPoint*$recevrCount)) {
                    return response()->json(['message'=>"Insufficient overall balance", 'status'=>'error']);
                }
            }
        }
       
        if(!empty($receiverIds)){

            foreach ($receiverIds as $key => $receiverid) {

                $sendToUser = ProgramUsers::find($receiverid);
                    
                DB::beginTransaction();

                try {

                    $campaign_budget = UserCampaignsBudget::select('budget')->where('program_user_id',$request->sender_id)->where('campaign_id',$campaign_id)->latest()->first();
                    $campaign_budget_bal =  $campaign_budget->budget ? $campaign_budget->budget : 0;

                    /****** Point Deduction *******/

                    if($budget_type == 1 && $points_allowed == 1){ // For Ripple
                        $email_address = $senderUser->email;
                        //$rippleBudget = $this->repository->getRippleBudget($email_address);
                        $currentBud = $campaign_budget_bal;
                        $finalBud = $currentBud-$inputPoint;
                        // Update sender Budget
                        

                         $updateSenderBudget = UserCampaignsBudget::where([

                            'program_user_id' => trim($request->sender_id),
                            'campaign_id'=> trim($campaign_id)

                        ])->update(['budget' => $finalBud ]);


                    

                    } // End Ripple Budget


                    if($budget_type == 2 && $points_allowed == 1){ // For Overall Balance
                        $current_budget_bal = UsersPoint::select('balance')->where('user_id',$request->sender_id)->latest()->first();
                        $current_budget_bal = $current_budget_bal ? $current_budget_bal->balance : 0;
                        $finalBud = $current_budget_bal-$inputPoint;
                        // Update sender budget
                        $updateSenderBudget = UsersPoint::create([
                            'value'    => -$inputPoint, // +/- point
                            'user_id'    => $request->sender_id, // Receiver
                            'transaction_type_id'    => 10,  // For Ripple
                            'description' => '',
                            'balance'    => $finalBud, // After +/- final balnce
                        ]);
                    } // End Overall Budget


                    /********************* If No Approval Required ***************************/

                    if( $approval_request == 0 && $points_allowed == 1){

                        $current_budget_bal = UsersPoint::select('balance')->where('user_id',$receiverid)->latest()->first();
                        $current_budget_bal = $current_budget_bal ? $current_budget_bal->balance : 0;
                        $finalPoints = $current_budget_bal+$inputPoint;

                        if($budget_type == 1){

                             // For Logs
                        
                            $createRippleLog = UserCampaignsBudgetLogs::create([
                                'program_user_id' => $request->sender_id,
                                'campaign_id' => $campaign_id,
                                'budget' => $inputPoint,
                                'current_balance' => $campaign_budget_bal ? $campaign_budget_bal : 0,
                                'description' => "Budget dedcuted by e thank you",   
                                'created_by_id' =>  $request->sender_id,     
                            ]);




                        }
                        
                        //update receiver budget
                        $currentBud = UsersPoint::select('balance')->where('user_id',$receiverid)->latest()->first();
                        $currentBud = $currentBud ? $currentBud->balance : 0;
                        $finalPoints = $currentBud+$inputPoint;
                        $updateReciverBudget = UsersPoint::create([
                            'value'    => $inputPoint, // +/- point
                            'user_id'    => $receiverid, // Receiver
                            'transaction_type_id'    => 10,  // For Ripple
                            'description' => '',
                            'balance'    => $finalPoints, // After +/- final balnce
                            'created_by_id' => $request->sender_id // Who send
                        ]);


                    }

                    /********************* If Approval Required ***************************/
                  
                    if($approval_request == 1 && $points_allowed == 1){ 

                        $groupData = $this->repository->getLevel1Leads($receiverid); // 2 for L1 & 3 for L2


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

                        $user_nomination_data  = UserNomination::create([

                            'user'   => $sendToUser->account_id, // Receiver
                            'account_id' => $senderUser->account_id, // Sender
                            'group_id' => $groupId,
                            'points'  => $inputPoint,
                            'campaign_id' => $campaign_id,
                            'is_active'     => 1,
                            'level_1_approval' => $update_vale_l1,
                            'level_2_approval' => $update_vale_l2,
                            'point_type' => $budget_type
                            //'nomination_id' => $campaign_id,
                        ]);
                        $user_nomin_inserted_id = $user_nomination_data->id; 

                    }
                    
                    $EcardDataCreated = UsersEcards::create([
                        'ecard_id' => $request->ecard_id,
                        'sent_to' => $receiverid,
                        'campaign_id' => $campaign_id,
                        'image_message' => $request->image_message,
                        'sent_by' => $request->sender_id,
                        'points' => $inputPoint,
                        'send_type' => $request->send_type,
                        
                    ]);
                    $ecard_lat_inserted_id = $EcardDataCreated->id; 


                    if(isset($user_nomin_inserted_id)){
                        UserNomination::where([
                            'id' => $user_nomin_inserted_id
                        ])->update(['ecard_id' => $ecard_lat_inserted_id ]);
                    }
                     

                    /****** Card Module ******/

                    if($approval_request == 0){

                        $image_url = [
                            'banner_img_url' => env('APP_URL')."/img/emailBanner.jpg",
                        ];

            
                        $eCardDetails = Ecards::find($request->ecard_id);

                        $path = public_path().'/uploaded/e_card_images/new';
                        if(!File::exists($path)) {
                            File::makeDirectory($path, $mode = 0777, true, true);
                        }
                        $randm = rand(100,1000000);
                        $newImage = $randm.time().'-'.$eCardDetails->card_image;
                        $file_path = "/uploaded/e_card_images/new";

                        $prev_img = '/uploaded/e_card_images/'.$eCardDetails->card_image;
                        $prev_img_path = url($prev_img);
                        //$prev_img_path = env('APP_URL')."/uploaded/e_card_images/".$eCardDetails->card_image;

                        $update = UsersEcards::where('id',$ecard_lat_inserted_id)->update(['new_image'=>$newImage,'image_path'=>$file_path]);

                        if($update === 1){
                            $destinationPath = public_path('uploaded/e_card_images/new/'.$newImage);
                            $client = new \Pdfcrowd\HtmlToImageClient("visions5", "3d020236c9a48cf82c620797434b8803");
                            $client->setOutputFormat("png");
                            $output_stream = fopen($destinationPath, "wb");

                            $image = $client->convertStringToStream('<html>
                                <head>
                                    <style>
                                      body {
                                        min-height: 350px;
                                      }
                                    </style>
                                </head>
                                <body>
                                    <table width="650" cellpadding="0" style="font-family: arial; color: #333333; margin: auto; border: 1px solid #ddd; border-collapse: collapse;">
                                        <tr>
                                            <td>
                                                <table width="100%" style="border-collapse: collapse;">
                                                    <tr>
                                                        <td align="center" style="padding: 40px 20px;">
                                                            <table width="100%" style="background-image: url('.$prev_img_path.');background-size: contain;background-repeat: no-repeat;background-position: center; border-collapse: collapse; height: 456px;">
                                                                <tr>
                                                                    <td style="height: 100%; width: 100%; background-color: rgba(0,0,0,0); text-align: left;" valign="top">
                                                                        <h4 style="color: #000;font-size: 22px;max-width: 440px;margin: 135px auto 0;font-weight: normal;">'.$request->image_message.'</h4>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </body>
                                </html>',$output_stream);
                            fclose($output_stream);
                        }

                        $new_img = '/uploaded/e_card_images/new/'.$newImage;
                        $new_img_path = url($new_img);

                        $data = [
                            'email' => $sendToUser->email,
                            'username' => $sendToUser->first_name.' '. $sendToUser->last_name,
                            'card_title' => $eCardDetails->card_title,
                            'sendername' => $senderUser->first_name.' '. $senderUser->last_name,
                            'image' => env('APP_URL')."/uploaded/e_card_images/".$eCardDetails->card_image,
                            'image_message' => $request->image_message,
                            'color_code' => "#e6141a",
                            'new_image' => $newImage,
                            'file_path' => $file_path,
                            'full_img_path' => $new_img_path,
                            'link_to_ecard' => $new_img_path
                        ];
                        try {

                            Mail::send('emails.sendEcard', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
                                $m->from('info@meritincentives.com','Vodafone Egypt');    
                                $m->to($data["email"])->subject($data["card_title"].' Ecard!');
                            });


                            DB::commit();
                        } catch (\Exception $e) {
                           
                            DB::rollBack();
                            array_push($failed, $e->getMessage());
                        }
                    }else{
                        DB::commit();
                    }


                } catch (\Exception $e) {
                   
                    DB::rollBack();
                    array_push($failed, $e->getMessage());
                }
            }  // end foreach


           
            if(!empty($failed)) {
                return response()->json(['message'=>'E-Card/Ripple Effect points has not been sent to '.implode(", ",$failed).'. Please try again later.', 'status'=>'success']);
            } else {
                return response()->json(['message'=>'E-Card sent successfully.', 'status'=>'success']);
            }
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }

    }



    /**
     * API: Save Eligible users settings as per the Admin input
     * 
     */


    public function saveEligibleUsersSettings(Request $request)
    {
        try {

            $data = $request->all();
            $campaign_id = $data['campaign_id'];

            $rules = [
                'campaign_id' => 'required|integer|exists:value_sets,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        
        
            // Check If campaign setting are there or not
            if (CampaignSettings::where('campaign_id', '=', $campaign_id)->count() > 0) {

                // Update
                $rules = [
                    'campaign_id' => 'required|integer|exists:campaign_settings,campaign_id',
                ];

                $validator = \Validator::make($request->all(), $rules);

                 if ($validator->fails())
                        return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                $updated_arr = array();

                // SENDER

                if($request->type == 1){
                   
                    $updated_arr['s_eligible_user_option'] = $request->s_eligible_user_option;
                    $updated_arr['s_level_option_selected'] = $request->s_level_option_selected;
                    $updated_arr['s_user_ids'] = $request->s_user_ids;
                    $updated_arr['s_group_ids'] = $request->s_group_ids;
                    
                }
                
                if($request->type == 2){
                   
                    $updated_arr['receiver_users'] = $request->receiver_users;
                    $updated_arr['receiver_group_ids'] = $request->receiver_group_ids;
                

                }
               
                CampaignSettings::where('campaign_id', $campaign_id)->update($updated_arr);
            }

            return response()->json(['message' => 'Settings has been updated successfully.'], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        }
    }

}
