<?php

namespace Modules\User\Http\Controllers;

use App\Http\Resources\ProgramUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Account\Models\Account;
use Modules\Program\Http\Repositories\ProgramRepository;
use Modules\User\Exports\UserExport;
use Modules\User\Imports\UserImport;
use Modules\User\Models\ProgramUsers;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use Modules\Reward\Models\ProductOrder;
use Modules\Nomination\Models\UserNomination;
use Modules\Reward\Models\Product;
use Modules\User\Models\RippleBudgetLog;
use Modules\User\Models\UserRoles;
use Modules\User\Models\UserCampaignsBudget;
use Modules\User\Models\UserCampaignsBudgetLogs;
use Exception;
use Illuminate\Foundation\Console\Presets\React;
use Modules\Reward\Imports\CategoryImport;
use Modules\User\Models\UsersPoint;
use Modules\User\Transformers\UserCampaignTransformer;
use DB;
use Modules\User\Models\UsersGroupList;
use Helper;
use Modules\User\Exports\CampaignUserBudgetExports;
use Throwable;

class UserManageController extends Controller
{
    private $program_repository;

    public function __construct(ProgramRepository $program_repository)
    {
        $this->program_repository = $program_repository;
		$this->middleware('auth:api');
    }


    /**
     * @param $program_id
     * @return JsonResponse
     * @throws \Exception
     */
    public function download($program_id)
    {
        $program = $this->program_repository->find($program_id);

        $columns = [];

        $list = unserialize($program->registrationForm->form);

        foreach ($list as $key => $value) {
            if($list[$key]['is_hidden'] != 0)
                array_push($columns, $key);
        }


        $program_name = str_replace(' ', '', $program->name);

        $file_name = $program_name.'_UserImportCsvTemplate.csv';

        $new_csv = fopen(public_path($file_name) , 'w');

        fputcsv($new_csv, $columns);

        fclose($new_csv);

        return response()->json([
            'file_path' => url($file_name)
        ]);
    }


    /***
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function import(Request $request)
    {
        //todo move uploaded file to a folder
        $file = $request->file('users');
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'users' => 'required|file',
        ]);
        $uploaded = $file->move(public_path('uploaded/'.$request->program_id.'/users/csv/'), $file->getClientOriginalName());
        $users = Excel::toCollection(new UserImport(), $uploaded->getRealPath());
        $users = $users[0]->toArray();
//        chmod($uploaded->getRealPath(),0777);
        // $rules = [
        //     '*.email' => "required|email|unique:program_users,email|unique:accounts,email",
        //     '*.username' => "required|unique:program_users,username|unique:accounts,email",
        //     '*.communication_preference' => "in:email,sms",
        // ];
        // $validator = \Validator::make($users, $rules);
        // if ($validator->fails())
        //     return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        foreach ($users as $user){
            $email = $user['email'];
            $name = $user['first_name'] . ' ' . $user['last_name'];
            $userExist = Account::where('email', $email)->first();
            if(!$userExist){
                $newformat = date('Y-m-d');
                if($user['date_of_birth'] != ''){
                    $time = strtotime( $user['date_of_birth'] );
                    $newformat = date('Y-m-d',$time);
                }
                $password = Str::random(8);
                $account = Account::updateOrCreate([
                    'email' => $email,
                    'name' => $name,
                    'password' => $user['password'] ?? $password,
                    'contact_number' => $user['mobile'] ?? '',
                    'def_dept_id' => null,
                    'type' => 'user'
                ]);
                //get user group id
                $groupDetails = Role::where('name', $user['group_name'])->first();
                $roleDetails = UserRoles::where('name', $user['role_name'])->first();
                UsersGroupList::create([
                    'account_id' => $account->id,
                    'user_group_id' => $groupDetails['id'] ?? 1,
                    'user_role_id' => $roleDetails['id'] ?? 1,
                ]);
                //$this->sendPasswordCodeToAccount($email,$name,$password);
                ProgramUsers::create([
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'email' => $email,
                    'username' => $user['username'],
                    'title' => $user['title'] ?? '',
                    'company'     => $user['company'] ?? '',
                    'job_title'     => $user['job_title'] ?? '',
                    'address_1'     => $user['address_1'] ?? '',
                    'address_2'     => $user['address_2'] ?? '',
                    'postcode'     => $user['postcode'] ?? '',
                    'country'     => $user['country'] ?? '',
                    'telephone'     => $user['telephone'] ?? '',
                    'mobile'     => $user['mobile'] ?? '',
                    'date_of_birth'     => $newformat   ,
                    'communication_preference' => $user['communication_preference'] ?? 'email',
                    'language'     => 'en',
                    'town'     =>  '',
                    'account_id' => $account->id,
                    'program_id' => $request->program_id,
                    'ripple_budget' => 0,
                ]);
            }
        }
        return response()->json([
            'uploaded_file' => url('uploaded/'.$request->program_id.'/users/csv/'.$uploaded->getFilename()),
            'message' => 'Data Imported Successfully'
        ]);
    }


    /**
     * @param $program_id
     * @return JsonResponse
     */
    public function export($program_id)
    {
        $file = Carbon::now()->timestamp.'-AllUserData.csv';
        // $path = 'uploaded/'.$program_id.'/users/csv/exported/'.$file;
        $path = public_path('uploaded/'.$program_id.'/users/csv/exported/'.$file);
        // $responsePath = "/export-file/{$program_id}/{$file}";
        $responsePath = 'uploaded/'.$program_id.'/users/csv/exported/'.$file;
        // Excel::store(new UserExport(), $path);
        Excel::store(new UserExport(), 'uploaded/'.$program_id.'/users/csv/exported/'.$file, 'real_public');
        return response()->json([
            'file_path' => url($responsePath),
        ]);
    }


    public function validateCsvContent(Request $request)
    {
        $path = $request->file('csv_file')->getRealPath();
        if ($request->has('header')) {
            $data = Excel::load($path, function($reader) {})->get()->toArray();
        } else {
            $data = array_map('str_getcsv', file($path));
        }
        if (count($data) > 0) {
            if ($request->has('header')) {
                $csv_header_fields = [];
                foreach ($data[0] as $key => $value) {
                    $csv_header_fields[] = $key;
                }
            }
            $csv_data = array_slice($data, 0, 2);
            $csv_data_file = CsvData::create([
                'csv_filename' => $request->file('csv_file')->getClientOriginalName(),
                'csv_header' => $request->has('header'),
                'csv_data' => json_encode($data)
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function sendPasswordCodeToAccount($email,$name,$password): void
    {
        $subject ="Cleveland Clinic Abu Dhabi - New Account";

        $message = 'Hi '.$name.', <br><br> Welcome to AD PORT, <br/>Here is your password <b>'.$password.'</b> to get access of system please go to site or Use Reset Password to get new password  ';

        $token = Str::random(50);
        Mail::send(new \Modules\Nomination\Mails\SendMail($email,$token,$message,$subject));

    }

    public function importEmployeeApi(Request $request){
        $url = env('HRS_API_URL');
        $username = env('HRS_API_USER');
        $password = env('HRS_API_PASS');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            throw new Exception(curl_error($ch));
        }
        echo $response;
    }

    public function getDashboardDetails($program_id) {
        try {
            $users = ProgramUsers::join('accounts', 'program_users.account_id', '=', 'accounts.id')
            ->where('program_users.program_id', '=', $program_id)->count();

            $groupCount = Role::where('program_id', $program_id)->where('parent_id', 0)->count();

            $ordersCount = ProductOrder::where('status' , 1)->count();
            $recentFiveOrders = ProductOrder::select('product_orders.id', 'first_name', 'last_name', 'email', 'product_orders.status', 'products.name','product_orders.created_at','product_denominations.value as price', 'product_orders.quantity','product_denominations.points','currencies.code')->where('product_orders.status' , 1)->join('products', 'products.id', '=' ,'product_orders.product_id')->join('product_denominations', 'product_denominations.id', '=' ,'product_orders.denomination_id')->join('currencies', 'currencies.id', '=' ,'products.currency_id')->orderBy('product_orders.id', 'desc')->limit(5)->get();

            $recentFiveOrders_Arr = array();
            foreach($recentFiveOrders as $key=>$recentOrder){
                $recentFiveOrders_Arr[$key]['id'] = $recentOrder['id'];
                /****status****/
                $currentOrderStatus = 'Pending'; //status === 1 means pending
                if($recentOrder['status'] === 3){
                    $currentOrderStatus = 'Shipped';
                } elseif($recentOrder['status'] === 2){
                    $currentOrderStatus = 'Confirmed';
                } elseif($recentOrder['status'] === -1){
                    $currentOrderStatus = 'Cancelled';
                }
                $recentFiveOrders_Arr[$key]['current_status'] = $currentOrderStatus;
                $recentFiveOrders_Arr[$key]['order_number'] = 'ccad-00'.$recentOrder['id'];
                $recentFiveOrders_Arr[$key]['value'] = $recentOrder['code'].' '.$recentOrder['price'];
                $recentFiveOrders_Arr[$key]['points'] = $recentOrder['points'];
                $recentFiveOrders_Arr[$key]['quantity'] = $recentOrder['quantity'];
                $recentFiveOrders_Arr[$key]['first_name'] = $recentOrder['first_name'];
                $recentFiveOrders_Arr[$key]['last_name'] = $recentOrder['last_name'];
                $recentFiveOrders_Arr[$key]['email'] = $recentOrder['email'];
                $recentFiveOrders_Arr[$key]['name'] = $recentOrder['name'];
                $recentFiveOrders_Arr[$key]['created_at'] = date('F j, Y g:i a', strtotime($recentOrder['created_at']));
            }

            $recentFiveOrders = $recentFiveOrders_Arr;

            $recentFiveOrders_all = $recentFiveOrders;
            foreach ($recentFiveOrders_all as $key1 => $answer) {
                unset($recentFiveOrders_all[$key1]['id']);
            }

            foreach ($recentFiveOrders as $key => $value) {
                $recentFiveOrders_all[$key]['id'] = Helper::customCrypt($value['id']);
            }

            $approved = UserNomination::where(['level_1_approval' => 1])->orWhere(['level_2_approval' => 1])->count();
            $decline = UserNomination::where(['level_1_approval' => -1])->orWhere(['level_2_approval' => -1])->count();
            $pending = UserNomination::where(['level_1_approval' => 0, 'level_2_approval' => 0])->count();

            $activeProducts = Product::where(['status' => '1'])->count();

            $giftCartOrders = ProductOrder::join('products', 'product_orders.product_id', '=', 'products.id')
            ->where('products.type', '=', 'Digital')->where('product_orders.status', 1)->count();

            $physicalProductOrders = ProductOrder::join('products', 'product_orders.product_id', '=', 'products.id')
            ->where('products.type', '=', 'Physical')->where('product_orders.status', 1)->count();

            return response()->json([
                'employees' => $users,
                'user_groups' => $groupCount,
                'orders' => $ordersCount,
                'nominations' => [
                    'pending' =>$pending,
                    'approved' =>$approved,
                    'decline' =>$decline,
                ],
                'products' => [
                    'active_products' =>$activeProducts,
                    'gift_card_orders' =>$giftCartOrders,
                    'physical_product_orders' =>$physicalProductOrders,
                ],
                'recent_five_orders' => $recentFiveOrders_all,

            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 502);
        }
    }

    public function uploadUsersBudget(Request $request) {
        try {
            $loggedID = Helper::customDecrypt($request->logged_user_id);
            $request['logged_user_id'] = $loggedID;
            $rules = [
                'budget_file' => 'required',
                'logged_user_id' => 'required|exists:program_users,id',
                'type' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $file = $request->file('budget_file');

            $validator1 = \Validator::make(
                [
                    'budget_file'      => $file,
                    'extension' => strtolower($file->getClientOriginalExtension()),
                ],
                [
                    'budget_file'    => 'required',
                    'extension'      => 'required|in:csv,xlsx,xls,ods',
                ]
            );
            if ($validator1->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator1->errors()], 422);

            $uploaded = $file->move(public_path('uploaded/user_budget_file/'), $file->getClientOriginalName());

            $budgets = Excel::toCollection(new CategoryImport(), $uploaded->getRealPath());

            //$budgets = $budgets[0]->toArray();
            $budgets = array_slice($budgets[0]->toArray(), 1);

            $rules = [
                '*.1' => "required|numeric|min:0",
            ];

            $validator = \Validator::make($budgets, $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            if($request->type == 1){#Overall_budget

                $userNotExist = [];
                foreach ($budgets as $key =>  $budget){
                    ///if($key === 0) continue;
                    $date = date('Y-m-d h:i:s');
                    $programUser = ProgramUsers::where('email', $budget[0])->first();
                    
                    if(!empty($programUser)){

                        $get_lastUser = UsersPoint::where('user_id',$programUser->id)->latest('id')->first();
                        if(!empty($get_lastUser)){
                            $get_balance = $get_lastUser->balance;
                            $total_balace = (((int)$get_balance)+((int)$budget[1]));
                        }else{
                            $total_balace = ((int)$budget[1]);
                        }

                        UsersPoint::create([
                            'value' =>$budget[1],
                            'user_id' =>$programUser->id,
                            'description'=>'',
                            'transaction_type_id'=>8,
                            'balance'=>$total_balace,
                            'created_by_id'=>$request->logged_user_id,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                        $programUser->point_balance = $total_balace;
                        $programUser->save();

                    } else {
                        $userNotExist[] = $budget[0];
                    }

                }

                return response()->json([
                    'uploaded_file' => url('uploaded/user_budget_file/'.$uploaded->getFilename()),
                    'message' => 'Users budget uploaded successfully',
                    'user_not_exist' => 'Total users not matched = '.count($userNotExist),
                ]);

            }else{#user_campaign_budget

                $rules = [
                    'campaign_id' => 'required|exists:value_sets,id',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails())
                    return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                $userNotExist = [];
                foreach ($budgets as $key =>  $budget){
                    //if($key === 0) continue;
                    
                    $date = date('Y-m-d h:i:s');
                    $programUser = ProgramUsers::where('email', $budget[0])->first();
                    if(!empty($programUser)){

                        $get_lastUser = UserCampaignsBudget::where(['program_user_id' =>$programUser->id,'campaign_id' =>$request->campaign_id])->latest('id')->first();
                        if(!empty($get_lastUser)){
                            $get_budget = $get_lastUser->budget;
                            $total_budget = (((int)$get_budget)+((int)$budget[1]));

                            $update = UserCampaignsBudget::where(['program_user_id' =>$programUser->id,'campaign_id' =>$request->campaign_id])->update(['budget'=>$total_budget]);

                        }else{
                            $total_budget = ((int)$budget[1]);
                            UserCampaignsBudget::create([
                                'program_user_id' =>$programUser->id,
                                'campaign_id' =>$request->campaign_id,
                                'budget'=>$total_budget,
                                'description'=>$request->description,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ]);
                        }

                        UserCampaignsBudgetLogs::create([
                            'program_user_id' =>$programUser->id,
                            'campaign_id' =>$request->campaign_id,
                            'budget'=>$budget[1],
                            'current_balance'=>$total_budget,
                            'created_by_id'=>$request->logged_user_id,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);

                    }else {
                        $userNotExist[] = $budget[0];
                    }

                }

                return response()->json([
                    'uploaded_file' => url('uploaded/user_budget_file/'.$uploaded->getFilename()),
                    'message' => 'Users budget uploaded successfully',
                    'user_not_exist' => 'Total users not matched = '.count($userNotExist),
                ]);

            }


        } catch (\Throwable $th) {
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }
    }
    /**************************
    add user budget(individualy)
    ***************************/
    public function addUserCampaignsBudget(Request $request,$id = null) {
        try{
            if($id){
                $id = Helper::customDecrypt($id);
            }
            //$request['campaign_id'] =  Helper::customDecrypt($request->campaign_id);
            $request['logged_user_id'] =  Helper::customDecrypt($request->logged_user_id);
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
        try {
            $date = date('Y-m-d h:i:s');
            if($id == null){
                $rules = [
                    'email' => 'required|exists:program_users,email',
                    'points' => 'required|numeric|min:0',
                    'campaign_id' => 'required|exists:value_sets,id',
                    //'logged_user_id' => 'required|exists:program_users,id',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails())
                    return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);


                $programUser = ProgramUsers::where('email', $request->email)->first();
                if(!empty($programUser)){

                    $get_lastUser = UserCampaignsBudget::where(['program_user_id' =>$programUser->id,'campaign_id' =>$request->campaign_id])->latest('id')->first();
                    if(!empty($get_lastUser)){
                        $get_budget = $get_lastUser->budget;
                        $total_budget = (((int)$get_budget)+((int)$request->points));
                        $current_balance = $get_budget;
                        $update = UserCampaignsBudget::where(['program_user_id' =>$programUser->id,'campaign_id' =>$request->campaign_id])->update(['budget'=>$total_budget,'description'=>$request->description]);

                    }else{
                        $total_budget = ((int)$request->points);
                        $current_balance = 0;
                        UserCampaignsBudget::create([
                            'program_user_id' =>$programUser->id,
                            'campaign_id' =>$request->campaign_id,
                            'budget'=>$total_budget,
                            'description'=>$request->description,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]);
                    }

                    UserCampaignsBudgetLogs::create([
                        'program_user_id' =>$programUser->id,
                        'campaign_id' =>$request->campaign_id,
                        'budget'=>$request->points,
                        'current_balance'=>$current_balance,
                        'description'=>$request->description,
                        'created_by_id'=>$request->logged_user_id,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);


                    return response()->json([
                        'status' => 'success',
                        'message' => 'Users budget added successfully',
                    ]);
                }else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User does not exists',
                    ]);
                }
            }else{
                $rules = [
                    'points' => 'required|numeric|min:0',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails())
                    return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                $get_budget = UserCampaignsBudget::where(['id' =>$id])->first();
                if(!empty($get_budget)){

                    $update = UserCampaignsBudget::where(['id'=>$id])->update(['budget'=>$request->points,'description'=>$request->description]);


                    UserCampaignsBudgetLogs::create([
                        'program_user_id' =>$get_budget->program_user_id,
                        'campaign_id' =>$get_budget->campaign_id,
                        'budget'=>$request->points,
                        'current_balance'=>$get_budget->budget,
                        'description'=>$request->description,
                        'created_by_id'=>$get_budget->created_by_id,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);


                    return response()->json([
                        'status' => 'success',
                        'message' => 'Users budget updated successfully',
                    ]);

                }else{
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Wrong id',
                    ]);
                }


            }



        } catch (\Throwable $th) {
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }
    }

    /**********************************
    fn to get list of all user's budget
    ***********************************/
    public function listUserCampaignsBudget($campaign_id = null){

        try{

            if($campaign_id == null){
                $get_userBudget = UserCampaignsBudget::with(['user'])->paginate('10');
                return fractal($get_userBudget, new UserCampaignTransformer());
            }else{
                $get_userBudget = UserCampaignsBudget::select('user_campaigns_budget.*')
                ->with(['user'])
                ->leftJoin('program_users', 'program_users.id', '=', 'user_campaigns_budget.program_user_id')
                ->where('user_campaigns_budget.campaign_id','=',$campaign_id)
                ->orderBy('program_users.first_name','ASC')
                ->paginate('10');
                return fractal($get_userBudget, new UserCampaignTransformer());
            }
            /*return response()->json([
                'status' => 'success',
                'message' => 'Get successfully',
                'data' => $get_userBudget,
            ]);*/
        }catch (\Throwable $th) {
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }
    }/********End function*********/

    public function getUserRoles(Request $request) {
        $userRoles = UserRoles::all();
        if($userRoles) {
            return response()->json(['data'=>$userRoles, 'message'=>'Data listed successfully.', 'status'=>'success']);
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }
    }

    public function AddUserSuggestion(Request $request){
        die('welcome');
    }

	/***********Start Campaign User Budget Export************/
	
	public function UserBudgetExport(Request $request)
    {
		$rules = [
			'campaignID' => 'required|integer|exists:value_sets,id',
		];

		$input = $request->all();
		$validator = \Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
		}
		else
		{
			if(isset($input['campaignID']) && !empty($input['campaignID']))
			{
				$file = Carbon::now()->timestamp.'-CampaignUserBudget.xlsx';				
				$path = public_path('uploaded/campaign/userbudget/'.$file); 
				$responsePath = 'uploaded/campaign/userbudget/'.$file;  
				Excel::store(new CampaignUserBudgetExports($input), 'uploaded/campaign/userbudget/'.$file, 'real_public');
				return response()->json([
					'file_path' => url($responsePath),
				]);
				
				//return Excel::download(new CampaignUserBudgetExports($input), $file);
			}
			else
			{
				return response()->json(['message' => 'campaign ID is missing'], 422);
			}
		}
    }

	/***********End Campaign User Budget Export************/
	
}
