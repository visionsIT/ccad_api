<?php

namespace Modules\Nomination\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel;
use Modules\Account\Models\Account;
use Modules\Account\Models\AccountBadges;
use Modules\Nomination\Exports\ReportExports;
use Modules\Nomination\Http\Services\NominationService;
use Modules\Nomination\Http\Services\UserNominationService;
use Modules\Nomination\Models\Nomination;
use Modules\Nomination\Models\UserNomination;
use Modules\User\Http\Services\UserService;
use Modules\User\Transformers\UserTransformer;
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
use Modules\User\Models\ProgramUsers;
use Modules\User\Http\Services\PointService;
use Modules\Account\Http\Services\AccountService;


class UserNominationBKPController extends Controller
{
    private $repository;
    private $nomination_service;
    private $point_service;
    private $user_nomination_service, $nomination_repository, $user_service;
    private $account_service;


    public function __construct(UserNominationRepository $repository,PointService $point_service,NominationRepository $nomination_repository,NominationService $nominationService,UserNominationService $user_nomination_service, UserService $user_service, AccountService $account_service)
    {
        $this->repository = $repository;
        $this->nomination_repository = $nomination_repository;
        $this->nomination_service = $nominationService;
        $this->user_nomination_service = $user_nomination_service;
        $this->user_service = $user_service;
        $this->point_service = $point_service;
        $this->account_service = $account_service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $order = $this->repository->getDesc();
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

//        $user = $this->user_service->find($request->user);

//        if ($user->account)
//            $request->request->add(['account_id' => $user->account->id]);
//        else
//            throw ValidationException::withMessages([ 'account' => 'The selected user not active' ]);

        $user_nomination = $this->repository->create($request->all());

//        $nomination = $this->nomination_repository->find($user_nomination->nomination_id);

//        $approvals = $this->nomination_service->getFirstLevelApprovalUsers($nomination);

        $approvals = $this->nomination_service->getApprovalAdmin($user_nomination);


        if( sizeof($approvals) > 0 )
        {
            $this->confirm_nomination($user_nomination, $approvals);
        }

        return fractal($user_nomination, new UserNominationTransformer);
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

        $subject ="Cleveland Clinic Abu Dhabi - Nomination submitted ";

        $message ="Thank you for your nomination! We will inform you if the nomination is approved.";

        $this->nomination_service->sendmail($sender_email,$subject,$message);


        // send to approver

        // $nominated_by_group_name= $user_nomination->nominated_user_group_name;

        $subject = "Cleveland Clinic Abu Dhabi - Nomination for approval";

        $link = env('APP_URL')."/approvals/approve-level-1";

        $message = "Please approve {$user_name} nomination for the {$value} value which has been submitted by {$sender} for the following reason: {$reason} \n\r <br> \n\r <br>";

        $message .="Once approved {$user_name} will receive {$level} points to their account. \n\r <br> \n\r <br> ";


        //$message .= "Dear {$nominated_by_group_name}, please approve \n\r <br>";

        $message .= "<a href=".$link.">Click here to approve this nomination</a> <br>";

        $message .= "Please approve or decline only nomination for people reporting to you \n\r <br>";


        foreach ($approvals as $account)
        {
            $this->nomination_service->sendmail($account->email,$subject,$message);
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

        $subject="Cleveland Clinic Abu Dhabi - Nomination submitted";

        $link = env('APP_URL')."/approvals/approve-level-2";

        //$nominated_by_group_name= $user_nomination->nominated_user_group_name;

        $message = "Please approve {$user_name} nomination for the {$value} value which has been submitted by {$sender} for the following reason: {$reason} \n\r <br> \n\r <br>";

        $message .="Once approved {$user_name} will receive {$level} points to their account. \n\r <br> \n\r <br> ";

        //$message .= "Dear {$nominated_by_group_name}, please approve \n\r <br>";

        $message .= "<a href=".$link.">Click here to approve this nomination</a> <br>";

        $message .= "Please approve or decline only nomination for people reporting to you \n\r <br>";


        foreach ($approvals as $account)
        {
            $this->nomination_service->sendmail($account,$subject,$message);
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

        $user_nomination = $this->user_nomination_service->find($id);
        $nomination = $this->nomination_service->find($user_nomination->nomination_id);
        $times=$user_nomination->type->times;
        $points = $user_nomination->level->points;


        $this->repository->update(['level_1_approval' => $request->level_1_approval], $id);

        // todo all second level users collection to be notify
        if ($nomination->approval_level === 'approval_level_2')
//            $users = $this->nomination_service->getSecondLevelApprovalUsers($user_nomination);
            $users = $this->nomination_service->getApprovalAdmin($user_nomination);
        else
        {
            if($request->level_1_approval==1){
                $data['value']       = $points;
                $data['description'] = '';

                $user = ProgramUsers::where('account_id',$user_nomination->user)->first(); // todo remind omda & mahmoud this is error

                $this->point_service->store($user, $data);

                // confirm nominator that nomination approve

                $sender_email = $user_nomination->account->email;

                $subject ="Cleveland Clinic Abu Dhabi - Your nomination was approved!";

                $message ="Your nomination has been approved. Thank you for your contribution.";

                $this->nomination_service->sendmail($sender_email,$subject,$message);


                // congratulate user that nomination approve

                $sender_email = $user_nomination->user_relation->email;

                $subject ="Cleveland Clinic Abu Dhabi - Congratulations!";

                $message ="Congratulations! You have been nominated. \n\r <br>  Please check Kafu wall of heroes to see who nominated you ";

                $message .="<a href='".env('APP_URL')."/wall-of-fame'>Click here to check your nomination</a> ";

                $this->nomination_service->sendmail($sender_email,$subject,$message);


                // Block of code to check the user has the count to get the badge
                {
                    $count = UserNomination::where(['user' => $user_nomination->user,'level_1_approval'=>1, 'value' => $user_nomination->value])->count();
                    if ($count == $times)
                    {
                        $data2['value']       =1500;
                        $data2['description'] = '';

                        $this->point_service->store($user, $data2);

                        AccountBadges::create([
                            'account_id' => $user_nomination->account_id, //todo account for nominated user
                            'nomination_type_id' => $user_nomination->value
                        ]);
                    }

                }
            }
        }
        return response()->json(['Data Updated Successfully']);
    }


    public function testMail(): JsonResponse
    {
        $sender_email = "e.mahmoud124@gmail.com";
        $subject ="Cleveland Clinic Abu Dhabi - Your nomination was approved!";
        $message ="Your nomination has been approved. Thank you for your contribution.";
        $this->nomination_service->sendmail($sender_email,$subject,$message);
        return response()->json(['Mail sent']);

    }


    public function updateLevelTwo(Request $request, $id): JsonResponse
    {

        $this->repository->update(['level_2_approval' => $request->level_2_approval], $id);
        $user_nomination = $this->user_nomination_service->find($id);
        $points = $user_nomination->level->points;
        $times=$user_nomination->type->times;

        if($request->level_2_approval==1){
            $data['value']       = $points;
            $data['description'] = '';
            $user = ProgramUsers::where('account_id',$user_nomination->user)->first();

            $this->point_service->store($user, $data);
        }

        if($request->level_2_approval==1){

            $data['value']       = $points;
            $data['description'] = '';
            $user = ProgramUsers::where('account_id',$user_nomination->user)->first(); // todo remind omda & mahmoud this is error

            // there is no error ya kenany
            $this->point_service->store($user, $data);

            // confirm nominator that nomination approve
            $sender_email = $user_nomination->account->email;

            $subject ="Cleveland Clinic Abu Dhabi - Your nomination was approved!";

            $message ="Your nomination has been approved. Thank you for your contribution.";

            $this->nomination_service->sendmail($sender_email,$subject,$message);


            // congratulate user that nomination approve

            $sender_email = $user_nomination->user_relation->email;

            $subject ="Cleveland Clinic Abu Dhabi - Congratulations!";

            $message ="Congratulations! You have been nominated. \n\r <br> Please check Kafu wall of heroes to see who nominated you ";

            $message .="<a href='".env('APP_URL')."/wall-of-fame'>Click here to check your nomination</a> ";

            $this->nomination_service->sendmail($sender_email,$subject,$message);

            // Block of code to check the user has the count to get the badge
            {
                $count = UserNomination::where(['user' => $user_nomination->user,'level_2_approval'=>1, 'value' => $user_nomination->value])->count();
                if ($count == $times)
                {
                    $data2['value']       =1500;
                    $data2['description'] = '';

                    $this->point_service->store($user, $data2);

                    AccountBadges::create([
                        'account_id' => $user_nomination->account_id, //todo account for nominated user
                        'nomination_type_id' => $user_nomination->value
                    ]);
                }

            }

        }


        return response()->json(['Data Updated Successfully']);
    }

    /**
     * @param $nomination_id
     * @param Account $account_id
     * @return Fractal
     */
    public function getUsersBy($nomination_id, Account $account_id)
    {
        $approved = UserNomination::where([
            'nomination_id' => $nomination_id,
            'level_1_approval' => 0,
            'level_2_approval' => 0,
        ])
            ->orderBY('id','desc')
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
                optional($value->type->valueset)->name,
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
        if($vpaccount->hasRole('ADPortVP')){ // only VP can nominate

            $users = $request->get('users');
            $data = [
                'nomination_id'     =>  $request->get('nomination_id'),
                'account_id'        =>  $vpaccount->id,//$loggedin_user->id,//$request->get('account_id'),
                'project_name'      =>  $request->get('project_name'),
                'reason'            =>  $request->get('reason'),
                'level_1_approval'  =>  0,
                'level_2_approval'  =>  0,
                'team_nomination'   =>  UserNomination::TEAM_NOMINATION
            ];


            foreach ($users as $key => $value) {

                $useracc = $this->account_service->show($value['accountid']);
                if( $vpaccount->def_dept_id == $useracc->def_dept_id ) {

                    $data['points'] = $value['value'];
                    $data['value'] = $value['value'];
                    $data['user'] = $value['accountid'];
                    // $data['project_name'] = $value['project_name'];
                    // $data['reason'] = $value['reason'];
                    $user_nomination = $this->repository->create($data);
                        //if(!empty($user_nomination))
                        //$user_nomination->sendEmail($this->nomination_service); // NO need individual notification on request
                    }
            }
            $status = true;

            $sender_email = 'Maitha.almurar@visions.net.in';//$user_nomination->account->email;

            $subject ="Cleveland Clinic Abu Dhabi - New project nomination!";
            $message = "Dear " . "VP of HR" .  "\n\r <br>";
            $a = count($users);
            $b = $user_nomination->project_name;
            $c =  $user_nomination->account->name;
            $d = $user_nomination->account->defaultDepartment->name;
            $message .="There are " . count($users) . "  nomination(s) for the " . $user_nomination->project_name . " project which has been submitted by " . $user_nomination->account->name . " - VP of " . $user_nomination->account->defaultDepartment->name . ", for the following reason: " . $user_nomination->reason . ".";
            $message .= "\n\r <br> Once approved  each nominee will receive " . $user_nomination->value . " points to their account.";
            $message .= "\n\r <br> *Click <a href='https://kafu.meritincentives.com/NominationApprove'>here</a> to review and approve this nomination*";


            $this->nomination_service->sendmail($sender_email,$subject,$message);



            $message = 'Team Nominations Submitted Successfully.';
        }
        else {
            $message = 'Error! Only VP can nominate employees under him!';
        }


        return response()->json(['message'=>$message,'status'=>$status]);
    }

    /**
     * @param $nomination_id
     * @param Account $account_id
     * @return Fractal
     */
    public function pendingApprovals($nomination_id, Request $request)
    {
        $response = [
            'status'    =>  false,
            'message'    =>  'Invalid Access Token!',
        ];
        $loggedin_user = $request->user();
        $approved = [];

            $status = true;
            $message = 'Team Nominations Submitted Successfully.';
            $status = $request->get('status');
            $search = $request->get('keyword');

            $useraccount = \Auth::user();
            $userdpt =  $useraccount->def_dept_id;

            $approved = null;

            if  ($useraccount->hasRole('ADPortHR')){

                            $approved = UserNomination::where([
                                'nomination_id' => $nomination_id,
                                'level_1_approval'  => $status,
                                'level_2_approval'  => 0,
                                'team_nomination'   => UserNomination::TEAM_NOMINATION,
                            ])->orderBY('id','desc')->paginate(10);

            } else if($useraccount->hasRole('ADPortVP')){

                $approved = UserNomination::join('accounts', 'user_nominations.user', '=', 'accounts.id')
                ->where('accounts.def_dept_id', '=', $userdpt)
                ->where([
                    'nomination_id' => $nomination_id,
                    'level_1_approval'  => $status,
                    'level_2_approval'  => 0,
                    'team_nomination'   => UserNomination::TEAM_NOMINATION,
                ])->select('accounts.id AS acc_id', 'user_nominations.*')
                ->orderBY('user_nominations.id','desc')->paginate(10);



            }


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

                $subject ="Cleveland Clinic Abu Dhabi - Your nomination was approved!";
                $message = "Dear " . $user_nomination->account->name .  "\n\r <br>";

                $message .="Your nomination  for the " . $user_nomination->project_name . " project has been successfully approved! As a result, " . $user_nomination->nominated_account->name . " has been successfully awarded with " . $user_nomination->value  . " to their Kafu account.";

                $message .="\n\r <br> To view this award on the Kafu wall of fame, please Click  <a href='".env('APP_URL')."/wall-of-fame'>here</a>.";


                $this->nomination_service->sendmail($sender_email,$subject,$message);


                // congratulate user that nomination approve

                $sender_email = $user_nomination->nominated_account->email;

                $subject ="Cleveland Clinic Abu Dhabi - Congratulations!";
                $message = "Dear " . $user_nomination->nominated_account->name ;
                $message .="\n\r <br> Congratulations! \n\r <br> Your diligence and dedication towards the " . $user_nomination->project_name . " project, have played a tremendous role towards its success!";
                $message .= "\n\r <br> As a sign of gratitude, you have been awarded with " . $user_nomination->value  . " to your Kafu account.";
                $message .= "\n\r <br>  *Click <a href='".env('APP_URL')."/wall-of-fame'>here</a> to view more details on why you have been awarded, and <a href='".env('APP_URL')."/page/rewards'>here</a>  to spend your points towards an exciting catalogue of rewards!*";
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

                $subject ="Cleveland Clinic Abu Dhabi - Your nomination was declined !";
                $message = "Dear " . $user_nomination->account->name ;
                $message .="\n\r <br> Your nomination " . $user_nomination->nominated_account->name . " for the " . $user_nomination->project_name . " project has been declined for the following reason: " . $request->reason ." .";
                $message .="\n\r <br> We encourage you to continue nominating your peers on Kafu, to help spread a positive and empowering culture in AD Ports. You may login and nominate by clicking <a href='".env('APP_URL')."/wall-of-fame'>here</a>.";
                //$message .="To view this award on the Kafu wall of fame, please <a href='".env('APP_URL')."/wall-of-fame'>Click here</a>.";

                $this->nomination_service->sendmail($sender_email,$subject,$message);

                return response()->json(['Rejection Complateted.']);
            }
        }

        return response()->json(['Error! Only VP of HR can Reject.']);
    }
}
