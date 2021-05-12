<?php namespace Modules\CommonSetting\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Program\Models\Currency;
use Modules\CommonSetting\Models\PointRateSettings;
use Modules\Reward\Models\Product;
use Modules\Reward\Models\ProductDenomination;
use Validator;
use Spatie\Permission\Models\Role;
use DB;
use Modules\CommonSetting\Repositories\CommonSettingsRepository;
use Modules\CommonSetting\Http\Services\CommonService;
use Helper;
use Response;
use Carbon\CarbonPeriod;
use DateTime;
use Modules\Nomination\Models\ValueSet;
use Modules\Nomination\Models\UserNomination;
use Modules\User\Models\UsersGroupList;
use File;
use Modules\Reward\Models\ProductOrder;
use Modules\User\Models\ProgramUsers;
use Carbon\Carbon;
class CommonSettingController extends Controller
{
    public function __construct(CommonSettingsRepository $common_repository,CommonService $common_service)
    {
        $this->middleware('auth:api', ['except' => ['loginVisit']]);
        $this->common_service = $common_service;
        $this->common_repository = $common_repository;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        //return view('commonsetting::index');
    }

    public function saveGroupList($group_id = null, $role_id = null){
        try{
            if($group_id == null || $role_id == null){
                echo "error";
            }else{
                $get_data = DB::table('model_has_roles')
                    ->where('role_id', $group_id)->get()->toArray();
                $date = date('Y-m-d h:i:s');

                if(!empty($get_data)){
                    foreach($get_data as $key=>$value){
                        $account_id = $value->model_id;

                        $check_account_id = DB::table('accounts')->where('id',$account_id)->get()->toArray();

                        if(!empty($check_account_id)){
                            $group_id = $value->role_id;

                            $check_alreadyExists = DB::table('users_group_list')->where(['account_id' =>$account_id, 'user_group_id' =>$group_id,'user_role_id'=>$role_id])->get()->toArray();

                            if(empty($check_alreadyExists)){
                                DB::table('users_group_list')->insert(['account_id' =>$account_id, 'user_group_id' =>$group_id,'user_role_id'=>$role_id,'status'=>'1','created_at'=>$date,'updated_at'=>$date]);
                            }
                        }

                    }

                    return response()->json(['message'=>'Save Successfully.', 'status'=>'success']);exit;
                }
            }

        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error' ,'errors' => $th->getMessage()]);
        }

    }

    public function getCurrencies(){
        try{
            $currency_list = DB::table('countries')->select('id','currency_name as name','currency_code as code')->get();
            if($currency_list){
                return response()->json(['data'=>$currency_list, 'message'=>'Get List Successfully.', 'status'=>'success']);exit;
            }else{
                return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
            }

        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error' ,'errors' => $th->getMessage()]);
        }
    }

    /**************************
    fn to add/edit curreny pts
    **************************/
    public function saveCurrencyPoints(Request $request, $id = null){
        try {

            $date = date('Y-m-d h:i:s');

            if($id != null){

                $rules = [
                    'country_id' => 'required|integer|exists:countries,id',
                    'points' => 'required|numeric||min:1',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails())
                    return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                $newPoints = $request->points;
                if(isset($request->status) && $request->status != ''){
                    $update_array = array('points'=>$newPoints,'status'=>$request->status);
                }else{
                    $update_array =  array('points'=>$newPoints);
                }

                $update = PointRateSettings::where(['id'=>$id,'country_id'=>$request->country_id])->update($update_array);

                if($update){
                   // $check_products = Product::where('country_id',$request->currency_id)->get();
                    /*$country = $request->country_id;
                    DB::transaction(function ()  use ($newPoints,$country){
                       // if(!empty($check_products)){
                            //foreach($check_products as $key=>$val){
                                #update_product_denomination
                                $get_product_denomi = ProductDenomination::where('country_id',$country)->get();
                                if(!empty($get_product_denomi)){
                                    foreach($get_product_denomi as $key1=>$val1){
                                        $get_denomi_val = ProductDenomination::where('id',$val1->id)->first();
                                        if(!empty($get_denomi_val)){
                                            $newCalculation = (((float)$get_denomi_val->value)*((float)$newPoints));
                                            $update_denomi = array('points'=>$newCalculation);
                                            ProductDenomination::where('id',$val1->id)->update($update_denomi);
                                        }
                                    }
                                }
                            /////}
                        ///}
                    });
                    DB::commit();*/

                    return response()->json(['message'=>'Updated successfully.', 'status'=>'success']);exit;;
                }else{
                    return response()->json(['message'=>"Check id and try again.", 'status'=>'error']);
                }
            }else{

                $rules = [
                    'country_id' => 'required|unique:point_rate_settings,country_id|integer|exists:countries,id',
                    'points' => 'required|numeric||min:1',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails())
                    return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                $PointRateSettings = new PointRateSettings;
                $PointRateSettings->country_id = $request->country_id;
                $PointRateSettings->points = $request->points;
                if(isset($request->status) && $request->status != ''){
                    $PointRateSettings->status = $request->status;
                }
                $PointRateSettings->created_at = $date;
                $PointRateSettings->updated_at = $date;
                $PointRateSettings->save();

                return response()->json(['message'=>'Saved successfully.', 'status'=>'success']);exit;
            }


        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

    /**************************
    get list of currency points
    **************************/
    public function getCurrenciesPoints(){
        try{
            $currency_pts_list = PointRateSettings::with(['country'])->get();
            if($currency_pts_list){
                return response()->json(['data'=>$currency_pts_list, 'message'=>'Get List successfully.', 'status'=>'success']);exit;
            }else{
                return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
            }
        }catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

    /***********************************
    fn to change the status of currency pts
    ***************************************/
    public function saveCurrencyPointsStatus(Request $request){
        try {
            $rules = [
                'point_id' => 'required|integer|exists:point_rate_settings,id',
                'status' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $PointRateSettings = PointRateSettings::find($request->point_id);
            $PointRateSettings->status = $request->status;
            $PointRateSettings->save();

            return response()->json(['message'=>'Status has been changed successfully.', 'status'=>'success']);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

    public function getCurrenciesCalculations(Request $request){

        $rules = [
            'country_id' => 'required|integer|exists:point_rate_settings,country_id',
            'amount' => 'required|numeric',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        try{
            $get_currency_point = PointRateSettings::select('points','status')->where('country_id','=',$request->country_id)->first();

            if(!empty($get_currency_point) && $get_currency_point->status == '1'){
                $points = $get_currency_point->points;

                $total_amount = $request->amount * $points;

                return response()->json(['message'=>'Calculate Successfully.', 'status'=>'success','total_amount'=>$total_amount]);exit;
            }else{
                return response()->json(['message'=>'Currency points are not available.', 'status'=>'error']);exit;
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }

    }

    /**************************
    fn to make default currency
    ***************************/
    public function makeDefaultCurrency(Request $request){
        $rules = [
            'country_id' => 'required|integer|exists:point_rate_settings,country_id',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        try{

            PointRateSettings::where('default_currency','=','1')->update(['default_currency'=>'0']);

            $check_status = PointRateSettings::select('status')->where('country_id','=',$request->country_id)->first();

            if($check_status->status == '1'){
                PointRateSettings::where('country_id','=',$request->country_id)->update(['default_currency'=>'1']);

                return response()->json(['message'=>'Default Currency Set Successfully.', 'status'=>'success']);exit;
            }else{
                return response()->json(['message'=>'Currency points are not active.', 'status'=>'error']);exit;
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        //return view('commonsetting::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //return view('commonsetting::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
       // return view('commonsetting::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**********get group list of user**********/
    public function userGroups($accountid = null){
        try{
            $accountid =  Helper::customDecrypt($accountid);
            $groupData = $this->common_repository->getUserGroups($accountid);
            return response()->json(['message'=>'User Group List.', 'status'=>'success','data'=>$groupData]);exit;
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }/*****fn_ends****/

    /***************
    *overall report*
    ***************/
    public function overallReport(Request $request){

        try{
            $request['account_id'] =  Helper::customDecrypt($request->account_id);

            $rules = [
                'user_group' => 'required',
                'account_id' => 'required|integer|exists:accounts,id',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            #Get_group_for_this_users
            $group_id = array();
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($request->account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        $group_id[] = $groups['user_group_id'];
                    }
                }
            }else{
                $group_id = array($request->user_group);
            }

            $data = $this->common_service->filterUserOverallReport($request->all(),$group_id);
            return response()->json(['message'=>'Data get successfully.', 'status'=>'success','data'=>$data]);exit;


        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage(),'line'=>$th->getLine()]);
        }
    }/*****fn_ends_overall_report****/

    public function downloadOverallRegistrations(Request $request){
        try{
            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';
            $group_id = array();
            $file_name = "/reports/all-registered-user.csv";
            $file_link = env('APP_URL').$file_name;
            // $account_id = $request->account_id;
            $account_id = Helper::customDecrypt($request->account_id);
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        $group_id[] = $groups["user_group_id"];
                    }
                }
            }else{
                $group_id = array($request->user_group);
            }
            $ids = join(',',$group_id);

            $accounts = DB::select("SELECT account_id FROM users_group_list  WHERE created_at >= '$from' AND created_at <= '$to' AND   user_group_id  IN ($ids) GROUP BY account_id");

            if(!empty($accounts)){
                $all_accounts = array();
                foreach($accounts as $account_data){
                    $all_accounts[] = $account_data->account_id;
                }
            }
            $pdf_filter = $request->pdf_filter;

            if($pdf_filter == '1'){
                $pdf_filter = 'week';
                $columns = array(ucfirst($pdf_filter),'Name','Email', 'Username','Company','Country','Address','Communication Preference','Created Date');
                $data = DB::table('accounts')->select('id','last_login','created_at',DB::raw("CONCAT_WS('-',week(created_at),YEAR(created_at)) as weekyear"))
                ->whereIn('id',$all_accounts)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','asc')->get()->groupBy("weekyear")->toArray();
            }
            else if($pdf_filter == '2'){
                $pdf_filter = 'month';
                $columns = array(ucfirst($pdf_filter),'Name','Email', 'Username','Company','Country','Address','Communication Preference','Created Date');
                $data = DB::table('accounts')->select('id','last_login','created_at',DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))
                ->whereIn('id',$all_accounts)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','asc')->get()->groupBy("monthyear")->toArray();
            }
            else if($pdf_filter == '0'){
                $pdf_filter = 'date';
                $columns = array(ucfirst($pdf_filter),'Name','Email', 'Username','Company','Country','Address','Communication Preference','Created Date');
                $data = DB::table('accounts')->select('id','last_login','created_at',DB::raw("date(created_at) as date"))
                ->whereIn('id',$all_accounts)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','asc')->get()->groupBy("date")->toArray();
            }


            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);

            foreach($data as $key => $value) {
                if($pdf_filter == 'week'){
                    $date_array = explode("-",$key);
                    $dateTime = new DateTime();
                    $dateTime->setISODate($date_array[1], $date_array[0]);
                    $start= $dateTime->format('d-M-Y');
                    $dateTime->modify('+6 days');
                    $end = $dateTime->format('d-M-Y');
                    fputcsv($new_csv,array($start." To ".$end));
                }else if($pdf_filter == 'month'){
                    $month =  Carbon::createFromFormat('m-Y', $key)->format('M Y');
                    fputcsv($new_csv,array($month));
                }
                else{
                    fputcsv($new_csv,array($key));
                }
                foreach($value as $review){
                    $last_login = $review->last_login;
                    $last_login_date = $last_login;
                    $user_data = DB::table("program_users")->where('account_id',$review->id)->first();

                    if($last_login != ''){
                        $last_login = date('Y-m-d', strtotime($last_login));
                        $last_login = new DateTime(date($last_login));
                        $today = new DateTime(date("Y-m-d"));
                        $diff = $today->diff($last_login)->format("%a");
                        if($diff <= 3){
                            $user_status = "Active";

                        }else{
                            $user_status = "Inactive";
                        }
                    }else{
                        $user_status = "Inactive";
                        $last_login_date = "N/A";
                    }
                    fputcsv($new_csv, array("",$user_data->first_name." ".$user_data->last_name,$user_data->email,$user_data->username,$user_data->company,$user_data->country,$user_data->address_1." ".$user_data->town." ".$user_data->postcode,$user_data->communication_preference,$user_data->created_at));
                }
            }
            fclose($new_csv);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);

        }
        catch (\Throwable $th) {
            //return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);


            $columns = array('Name','email', 'username','Company','country','Address','Communication Preference','Group Name','Role Name','VP EMP Number','Created Date');
            $file_name = "/reports/all-registered-user.csv";
            $file_link = env('APP_URL').$file_name;
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);
        }

    }


    public function getCampaignList(){

        $campaigns = ValueSet::select('id','name')->where('status','1')->get();
        return response()->json(['message'=>'Campaign List.', 'status'=>'success','data'=>$campaigns]);exit;

    }

    /******************
    recognition report
    ******************/
    public function recognitionReport(Request $request){
        try{

            // $request['account_id'] = $request->account_id;
            $request['account_id'] =  Helper::customDecrypt($request->account_id);
            $rules = [
                'user_group' => 'required',
                'account_id' => 'required|integer|exists:accounts,id',
                'campaign_id' => 'required',
                'from'      => 'required|date_format:Y-m-d',
                'to'      => 'required|date_format:Y-m-d',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            #Get_group_for_this_users
            $group_id = array();
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($request->account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        if(!in_array($groups['user_group_id'],$group_id)){
                            $group_id[] = $groups['user_group_id'];
                        }

                    }
                }
            }else{
                $group_id = array($request->user_group);
            }

            $data = $this->common_service->filterUserRecognitionReport($request->all(),$group_id);
            return response()->json(['message'=>'Data get successfully.', 'status'=>'success','data'=>$data]);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage(),'line'=>$th->getLine()]);
        }
    }/*****fn_ends_recognition_report******/

    public function loginVisit(Request $request){
        try{

            //$request['account_id'] =  Helper::customDecrypt($request->account_id);
            $request['account_id'] = $request->account_id;
            $rules = [
                //'account_id' => 'required|integer|exists:accounts,id',
                'page_name' => 'required|in:login,my_activity,rewards'
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $ip_address = $request->ip();
            // $ip_address = '127.0.1';

            if(isset($request->account_id) && !empty($request->account_id)){
                $request['account_id'] =  Helper::customDecrypt($request->account_id);
                $count = DB::table('page_visits')->where('account_id',$request->account_id)->where('page_name',$request->page_name)->where('created_at', 'like', date('Y-m-d%'))->count();
                $old_count = DB::table('page_visits')->where('account_id',$request->account_id)->where('page_name',$request->page_name)->count();
                $account_id = $request->account_id;
            }
            else{
                $count = DB::table('page_visits')->where('user_ip',$ip_address)->where('page_name',$request->page_name)->where('created_at', 'like', date('Y-m-d%'))->count();
                $old_count = DB::table('page_visits')->where('user_ip',$ip_address)->where('page_name',$request->page_name)->count();
                $account_id = NULL;
            }
            if($count == 0){


                if($old_count > 0){
                    $is_unique = '0';
                }
                else{
                    $is_unique = '1';
                }
                $insert_data = [
                    'page_name' => $request->page_name,
                    'visits_count' => '1',
                    'user_ip' => $ip_address,
                    'account_id' => $account_id,
                    'is_unique' => $is_unique,
                    'created_at' =>date('Y-m-d h:i:s'),
                    'updated_at' =>date('Y-m-d h:i:s'),
                ];
                $insert =   DB::table("page_visits")->insert($insert_data);
                return response()->json(['message'=>'Successfully  executed.', 'status'=>'success']);
                exit;
            }
            else{
                if(isset($request->account_id)){
                    $old_data = DB::table("page_visits")->where('account_id',$account_id)->where('page_name',$request->page_name)->where('created_at', 'like', date('Y-m-d%'))->first();
                    $update_data = [
                        'visits_count' => $old_data->visits_count+1,
                        'updated_at' =>date('Y-m-d h:i:s'),

                    ];
                    $update = DB::table("page_visits")->where('account_id',$account_id)->where('page_name',$request->page_name)->where('created_at', 'like', date('Y-m-d%'))->update($update_data);
                }
                else{
                    $old_data = DB::table("page_visits")->where('user_ip',$ip_address)->where('page_name',$request->page_name)->where('created_at', 'like', date('Y-m-d%'))->first();
                    $update_data = [
                        'visits_count' => $old_data->visits_count+1,
                        'updated_at' =>date('Y-m-d h:i:s'),

                    ];
                    $update = DB::table("page_visits")->where(['user_ip'=>$ip_address,'account_id' => NULL])->where('page_name',$request->page_name)->where('created_at', 'like', date('Y-m-d%'))->update($update_data);
                }

                return response()->json(['message'=>'Successfully  executed.', 'status'=>'success']);
                exit;
            }
        }
        catch(\Throwable $th){
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
        }
    }

    public function loginVisitCsv(Request $request){
        try{
            if($request->pdf_filter == 0){
                $from = $request->from;
                $to = $request->to;
                $period = CarbonPeriod::create($from, $to)->toArray();
                foreach ($period as $date) {
                    $date_array[] =  $date->format('Y-m-d');
                }
                $all_days = join(',',$date_array);
                $data = DB::Table('page_visits')
                        ->Where(function ($query) use($date_array) {
                            for ($i = 0; $i < count($date_array); $i++){
                                $query->orwhere('created_at', 'like',  '%' . $date_array[$i] .'%');
                            }
                })->where('page_name','login')->orderBy('created_at','asc')->get()->toArray();
                foreach($data as $key=>$value){
                    $str = $value->created_at;
                    $date = substr($str, 0, strpos( $str, ' '));
                    $new_array[$date][] = $value;
                }
                $columns = array('DATE','User IP Address','Name','Email', 'Username','Company','Country','Address','Communication Preference','Total Visit Count');

                if (!file_exists('reports')) {
                    File::makeDirectory(public_path('reports'));
                }
                $file_name = "/reports/website-visit.csv";
                $file_link = env('APP_URL').$file_name;
                $new_csv = fopen(public_path($file_name) , 'w');
                fputcsv($new_csv, $columns);
                foreach($new_array as $key => $value) {
                    fputcsv($new_csv,array($key));
                    foreach($value as $value1){

                        $user_data = DB::table("page_visits")
                        ->select('page_visits.account_id','program_users.*')
                        ->leftJoin('program_users','page_visits.account_id','=','program_users.account_id')
                        ->where("page_visits.account_id",$value1->account_id)->orderBy('page_visits.id','desc')->first();
                        fputcsv($new_csv,array("",$value1->user_ip,$user_data->first_name." ".$user_data->last_name,$user_data->email,$user_data->country,$user_data->company,$user_data->country,$user_data->address_1." ".$user_data->town." ".$user_data->postcode,$user_data->communication_preference,$value1->visits_count));
                    }
                }
                fclose($new_csv);
                return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
                // //////
            }

            else if($request->pdf_filter == 2){
                $from = $request['from'] . ' 00:00:01';
                $to = $request['to'] . ' 23:59:59';
                $data = DB::table('page_visits')->select('account_id','user_ip','visits_count',DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))
                ->where('page_name','login')
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','desc')->get()->groupBy('monthyear')->toArray();

                foreach($data as $key=>$value){
                    foreach($value as $ky => $vl){
                        if(isset($new_array[$vl->monthyear][$vl->user_ip])){
                            foreach($new_array[$vl->monthyear][$vl->user_ip] as $ky1 => $vl1){
                                $vl1->visits_count += $vl->visits_count;
                            }
                        }
                        else{
                            $new_array[$vl->monthyear][$vl->user_ip][] = $vl;
                        }
                    }
                }


                $columns = array('Month','User IP Address','Name','email', 'username','Company','country','Address','Communication Preference','Total Login Count');
                if (!file_exists('reports')) {
                    File::makeDirectory(public_path('reports'));
                }
                $file_name = "/reports/website-visit.csv";
                $file_link = env('APP_URL').$file_name;
                $new_csv = fopen(public_path($file_name) , 'w');
                fputcsv($new_csv, $columns);
                foreach($new_array as $key => $value) {

                   $month =  Carbon::createFromFormat('m-Y', $key)->format('M Y');
                    fputcsv($new_csv,array($month));
                    foreach($value as $value1){
                        foreach($value1 as $ky => $vl){
                            if($vl->account_id != ''){
                                $user_data = DB::table("page_visits")
                                ->select('page_visits.account_id','program_users.*')
                                ->leftJoin('program_users','page_visits.account_id','=','program_users.account_id')
                                ->where("page_visits.account_id",$vl->account_id)->orderBy('page_visits.id','desc')->first();
                                fputcsv($new_csv,array("",$vl->user_ip,$user_data->first_name." ".$user_data->last_name,$user_data->email,$user_data->country,$user_data->company,$user_data->country,$user_data->address_1." ".$user_data->town." ".$user_data->postcode,$user_data->communication_preference,$vl->visits_count));
                            }
                            else{
                                fputcsv($new_csv,array("",$vl->user_ip,"","","","","","","",$vl->visits_count));
                            }
                        }
                    }
                }
                fclose($new_csv);
                return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
            }else{
                $from = $request['from'] . ' 00:00:01';
                $to = $request['to'] . ' 23:59:59';
                $data = DB::table('page_visits')->select('account_id','user_ip','visits_count',DB::raw("CONCAT_WS('-',week(created_at),YEAR(created_at)) as weekyear"))
                ->where('page_name','login')
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','desc')->get()->groupBy('weekyear')->toArray();
                foreach($data as $key=>$value){
                    foreach($value as $ky => $vl){
                        if(isset($new_array[$vl->weekyear][$vl->user_ip])){
                            foreach($new_array[$vl->weekyear][$vl->user_ip] as $ky1 => $vl1){
                                $vl1->visits_count += $vl->visits_count;
                            }
                        }
                        else{
                            $new_array[$vl->weekyear][$vl->user_ip][] = $vl;
                        }
                    }
                }
                $columns = array('Week','User IP Address','Name','email', 'username','Company','country','Address','Communication Preference','Total Login Count');
                if (!file_exists('reports')) {
                    File::makeDirectory(public_path('reports'));
                }
                $file_name = "/reports/website-visit.csv";
                $file_link = env('APP_URL').$file_name;
                $new_csv = fopen(public_path($file_name) , 'w');
                fputcsv($new_csv, $columns);
                foreach($new_array as $key => $value) {
                    $date_array = explode("-",$key);
                    $dateTime = new DateTime();
                    $dateTime->setISODate($date_array[1], $date_array[0]);
                    $start= $dateTime->format('d-M-Y');
                    $dateTime->modify('+6 days');
                    $end = $dateTime->format('d-M-Y');
                    fputcsv($new_csv,array($start." To ".$end));
                    foreach($value as $value1){
                        foreach($value1 as $ky => $vl){
                            if($vl->account_id != ''){
                                $user_data = DB::table("page_visits")
                                ->select('page_visits.account_id','program_users.*')
                                ->leftJoin('program_users','page_visits.account_id','=','program_users.account_id')
                                ->where("page_visits.account_id",$vl->account_id)->orderBy('page_visits.id','desc')->first();
                                fputcsv($new_csv,array("",$vl->user_ip,$user_data->first_name." ".$user_data->last_name,$user_data->email,$user_data->country,$user_data->company,$user_data->country,$user_data->address_1." ".$user_data->town." ".$user_data->postcode,$user_data->communication_preference,$vl->visits_count));
                            }
                            else{
                                fputcsv($new_csv,array("",$vl->user_ip,"","","","","","","",$vl->visits_count));
                            }
                        }
                    }
                }
                fclose($new_csv);
                return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
            }
        }
        catch (\Throwable $th) {
            // return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
            $columns = array('Week','User IP Address','Name','email', 'username','Company','country','Address','Communication Preference','Total Login Count');
            if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/website-visit.csv";
            $file_link = env('APP_URL').$file_name;
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);
        }
    }

    public function activeInactive(Request $request){
        try{
            $group_id = array();
            if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/active-inactive-user.csv";
            $file_link = env('APP_URL').$file_name;
            // $account_id = $request->account_id;
            $account_id = Helper::customDecrypt($request->account_id);
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        $group_id[] = $groups["user_group_id"];
                    }
                }
            }else{
                $group_id = array($request->user_group);
            }

            $ids = join(',',$group_id);


            // dd($all_accounts);
            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';


            // All accounts ID

            $accounts = DB::select("SELECT account_id FROM users_group_list  WHERE created_at >= '$from' AND created_at <= '$to' AND   user_group_id  IN ($ids) GROUP BY account_id");

            if(!empty($accounts)){
                $all_accounts = array();
                foreach($accounts as $account_data){
                    $all_accounts[] = $account_data->account_id;
                }
            }


            $pdf_filter = $request->pdf_filter;
            if($pdf_filter == '3'){
                $data = DB::table('users_group_list')->select('users_group_list.account_id','users_group_list.created_at','users_group_list.user_group_id','users_group_list.user_role_id','program_users.*','user_roles.name as user_role_name','roles.name as group_name','accounts.email as vp_emp_email')
                ->leftJoin('program_users', 'users_group_list.account_id', '=', 'program_users.account_id')
                ->leftJoin('user_roles', 'users_group_list.user_role_id', '=', 'user_roles.id')
                ->leftJoin('roles', 'users_group_list.user_group_id', '=', 'roles.id')
                ->leftJoin('accounts', 'program_users.emp_number', '=', 'accounts.id')
                ->whereIn('users_group_list.user_group_id',$group_id)
                ->where('users_group_list.created_at', '>=', $from)
                ->where('users_group_list.created_at', '<=', $to)
                ->orderBy('users_group_list.created_at','asc')->get()->groupBy('account_id')->toArray();
                // dd($data);
                $columns = array('Name','email', 'username','Company','country','Address','Communication Preference','Group Name','Role Name','vp_emp_number','created_at','Status','Last Login');
                $new_csv = fopen(public_path($file_name) , 'w');
                fputcsv($new_csv, $columns);
                foreach($data as $review1) {
                    foreach($review1 as $review){
                        dd($review);
                        $last_login = DB::table('accounts')->where('id',$review->account_id)->first()->last_login;
                        $last_login_date = $last_login;

                        if($last_login != ''){
                            $last_login = date('Y-m-d', strtotime($last_login));
                            $last_login = new DateTime(date($last_login));
                            $today = new DateTime(date("Y-m-d"));
                            $diff = $today->diff($last_login)->format("%a");
                            if($diff <= 3){
                                $user_status = "Active";

                            }else{
                                $user_status = "Inactive";
                            }
                        }else{
                            $user_status = "Inactive";
                            $last_login_date = "N/A";
                        }
                        fputcsv($new_csv, array($review->first_name.' '.$review->last_name,$review->email, $review->username,$review->company_name,$review->country, $review->address_1." ".$review->town." ".$review->postcode,$review->communication_preference,$review->group_name,$review->user_role_name,$review->vp_emp_email,$review->created_at,$user_status,$last_login_date));
                    }
                }
                fclose($new_csv);
                return response()->json(['message' => 'success', 'status'=>'success', 'link' => $file_link]);

            }
            else if($pdf_filter == '1'){
                $pdf_filter = 'week';
                $columns = array(ucfirst($pdf_filter),'Name','Email', 'Username','Company','Country','Address','Communication Preference','Created Date','Status','Last Login');
                $data = DB::table('accounts')->select('id','last_login','created_at',DB::raw("CONCAT_WS('-',week(created_at),YEAR(created_at)) as weekyear"))
                ->whereIn('id',$all_accounts)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','asc')->get()->groupBy("weekyear")->toArray();
            }
            else if($pdf_filter == '2'){
                $pdf_filter = 'month';
                $columns = array(ucfirst($pdf_filter),'Name','Email', 'Username','Company','Country','Address','Communication Preference','Created Date','Status','Last Login');
                $data = DB::table('accounts')->select('id','last_login','created_at',DB::raw("CONCAT_WS('-',month(created_at),YEAR(created_at)) as monthyear"))
                ->whereIn('id',$all_accounts)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','asc')->get()->groupBy("monthyear")->toArray();
            }
            else if($pdf_filter == '0'){
                $pdf_filter = 'date';
                $columns = array(ucfirst($pdf_filter),'Name','Email', 'Username','Company','Country','Address','Communication Preference','Created Date','Status','Last Login');
                $data = DB::table('accounts')->select('id','last_login','created_at',DB::raw("date(created_at) as date"))
                ->whereIn('id',$all_accounts)
                ->where('created_at', '>=', $from)
                ->where('created_at', '<=', $to)
                ->orderBy('created_at','asc')->get()->groupBy("date")->toArray();
            }

            $file_name = $file_name;
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            foreach($data as $key => $value) {
                if($pdf_filter == 'week'){
                    $date_array = explode("-",$key);
                    $dateTime = new DateTime();
                    $dateTime->setISODate($date_array[1], $date_array[0]);
                    $start= $dateTime->format('d-M-Y');
                    $dateTime->modify('+6 days');
                    $end = $dateTime->format('d-M-Y');
                    fputcsv($new_csv,array($start." To ".$end));
                }else if($pdf_filter == 'month'){
                    $month =  Carbon::createFromFormat('m-Y', $key)->format('M Y');
                    fputcsv($new_csv,array($month));
                }
                else{
                    fputcsv($new_csv,array($key));
                }
                foreach($value as $review){
                    $last_login = $review->last_login;
                    $last_login_date = $last_login;
                    $user_data = DB::table("program_users")->where('account_id',$review->id)->first();

                    if($last_login != ''){
                        $last_login = date('Y-m-d', strtotime($last_login));
                        $last_login = new DateTime(date($last_login));
                        $today = new DateTime(date("Y-m-d"));
                        $diff = $today->diff($last_login)->format("%a");
                        if($diff <= 3){
                            $user_status = "Active";

                        }else{
                            $user_status = "Inactive";
                        }
                    }else{
                        $user_status = "Inactive";
                        $last_login_date = "N/A";
                    }
                    fputcsv($new_csv, array("",$user_data->first_name." ".$user_data->last_name,$user_data->email,$user_data->username,$user_data->company,$user_data->country,$user_data->address_1." ".$user_data->town." ".$user_data->postcode,$user_data->communication_preference,$user_data->created_at,$user_status,$last_login_date));
                }
            }
            fclose($new_csv);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
        }
        catch (\Throwable $th) {
            // return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
            $columns = array('Name','Email', 'Username','Company','Country','Address','Communication Preference','Status','Last Login');
            if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/active-inactive-user.csv";
            $file_link = env('APP_URL').$file_name;
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);
        }

    }





    public function nominationPointsCsv(Request $request){
        try{
            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';
            $group_id = array();

            if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/nomination_awards.csv";
            $file_link = env('APP_URL').$file_name;


            // $account_id = $request->account_id;
            $account_id = Helper::customDecrypt($request->account_id);
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        $group_id[] = $groups["user_group_id"];
                    }
                }
            }else{
                $group_id = array($request->user_group);
            }
            $pdf_filter = $request->pdf_filter;


            $nomination_data = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->with(['nominee_account','user_account','campaignid','groupName','valueCategory']);
            if($request->campaign_id != '0'){
                $nomination_data = $nomination_data->where('campaign_id',$request->campaign_id);
            }

            $final_data = $nomination_data->get()->toArray();


            // dd($final_data);
            $columns = array('Campaign Name','Nominator First Name','Nominator Surname', 'Nominator Email','Nominee First Name','Nominee Surname','Nominee Email','Nominee Function','Nominee User Group','L1AdminFirst Name','L1Admin Surname','L1Admin Email','L1Admin User Group','L2Admin First Name','L2Admin Surname','L2Admin Email','Value Category Name','Level Name','Requested Value','Value','Reason For Nomination','Status','Reason For Decline','Created On','Updated On');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            foreach($final_data as $key => $value) {

                // dd($value);
                if($value["ecard_id"] != ''){
                        $type = "Ecard";
                    }
                else{
                    $type = "Nomination";
                }

                if($value["points"] == ''){
                    $value["points"] = 0;
                }

                $l1admin_first_name = "N/A";
                $l1admin_last_name = "N/A";
                $l1admin_email = "N/A";

                $l2admin_first_name = "";
                $l2admin_last_name = "";
                $l2admin_email = "";
                $l1admin_group_name = "";
                $cnt = 0;
                $level_name = "Nomination";


                if($value["nominee_account"]["vp_emp_number"] != ''){

                    $adminL1_data = DB::table("users_group_list")
                                    ->select("users_group_list.user_group_id","users_group_list.account_id","roles.name","program_users.first_name","program_users.last_name","program_users.email")
                                    ->leftJoin('roles','users_group_list.user_group_id','=','roles.id')
                                    ->leftJoin('program_users','users_group_list.account_id','=','program_users.account_id')
                                    ->where(["users_group_list.account_id" => $value["nominee_account"]["vp_emp_number"],"users_group_list.user_role_id" => 2])->get()->toArray();
                                    // dd($adminL1_data);


                    if(count($adminL1_data) > 0){
                        foreach($adminL1_data as $key1 => $value1){
                            $l1admin_first_name = $value1->first_name;
                            $l1admin_last_name = $value1->last_name;
                            $l1admin_email = $value1->email;
                            if($cnt == 0){
                                $l1admin_group_name = $l1admin_group_name.$value1->name;
                            }
                            else{
                                $l1admin_group_name = $l1admin_group_name.", ".$value1->name;
                            }
                            $cnt++;
                        }
                    }


                }

                if(!isset($value["value_category"]["name"])){
                    $value["value_category"]["name"] = "N/A";
                }

                if($value["ecard_id"] != ''){
                    $level_name = "Ecard";
                }

                if($value["level_1_approval"] == 2 && $value["level_2_approval"] == 2 || $value["level_1_approval"] == 1 && $value["level_2_approval"]== 1 || $value["level_1_approval"] == 1 && $value["level_2_approval"] == 2 || $value["level_1_approval"] == 2 && $value["level_2_approval"] == 1 ){
                    if($value["level_1_approval"] == 2 && $value["level_2_approval"] == 2){
                        $status_by = "Sent";
                    }
                    else if($value["level_1_approval"] == 1 && $value["level_2_approval"] == 1){
                        $status_by = "Approved_l2";
                    }
                    else if($value["level_1_approval"] == 2 && $value["level_2_approval"] == 1){
                        $status_by = "Approved_l2";
                    }
                    else{
                        $status_by = "Approved_l1";
                    }
                }
                else if($value["level_1_approval"] == "-1" || $value["level_2_approval"] == "-1" ){
                    if($value["level_1_approval"] == "-1"){
                        $status_by = "Declined_l1";
                    }
                    else{
                        $status_by = "Declined_l2";
                    }
                }
                else if($value["level_1_approval"] == "0"){
                    $status_by = "Pending_l1";
                }
                else{
                    $status_by = "Pending_l2";
                }

                if($status_by == "Approved_l1" || $status_by == "Approved_l2" || $status_by == "Sent"){
                    $value_points = $value["points"];
                }
                else{
                    $value_points = 0;
                }

                if($value["group_id"] != '' && strpos($value["campaignid"]["name"], "Excellence Award") !== false){

                    $l2_admin = DB::table("program_users")
                                ->select("program_users.first_name","program_users.last_name","program_users.email","users_group_list.id")
                                ->leftJoin("users_group_list","program_users.account_id","=","users_group_list.account_id")
                                ->where(["user_group_id" => $value["group_id"],"user_role_id" => '3'])
                                ->first();

                        if(!empty($l2_admin)){
                            $l2admin_first_name = $l2_admin->first_name;
                            $l2admin_last_name = $l2_admin->last_name;
                            $l2admin_email = $l2_admin->email;
                        }

                }

                // if(strpos($value["campaignid"]["name"], "E-CARDS") !== false){
                //     $l1admin_first_name = "";
                //     $l1admin_last_name = "";
                //     $l1admin_email = "";

                //     $l2admin_first_name = "";
                //     $l2admin_last_name = "";
                //     $l2admin_email = "";
                //     $l1admin_group_name = "";
                // }

                if($type == 'Ecard'){
                    $l1admin_first_name = "";
                    $l1admin_last_name = "";
                    $l1admin_email = "";

                    $l2admin_first_name = "";
                    $l2admin_last_name = "";
                    $l2admin_email = "";
                    $l1admin_group_name = "";
                    $status_by = "Sent";

                    $ecard_id = $value["ecard_id"];
                    $value_points = $value["points"];

                    $ecard_detail = DB::table('users_ecards')
                    ->select("users_ecards.id","users_ecards.image_message","ecards.card_title")
                    ->leftJoin("ecards","users_ecards.ecard_id","=","ecards.id")
                    ->where('users_ecards.id',$ecard_id)
                    ->first();
                    $value["reason"] = $ecard_detail->image_message;
                    $value["value_category"]["name"] = $ecard_detail->card_title;

                }

                fputcsv($new_csv, array( $value["campaignid"]["name"],$value["user_account"]["first_name"],$value["user_account"]["last_name"],$value["user_account"]["email"],$value["nominee_account"]["first_name"],$value["nominee_account"]["last_name"],$value["nominee_account"]["email"],$value["nominee_function"],$value["group_name"]["name"],$l1admin_first_name,$l1admin_last_name,$l1admin_email,$l1admin_group_name,$l2admin_first_name,$l2admin_last_name,$l2admin_email,$value["value_category"]["name"],$level_name,$value["points"],$value_points,$value["reason"],$status_by,$value["reject_reason"],$value["created_at"],$value["updated_at"] ));


            }

            fclose($new_csv);
            // chmod(public_path($file_name),0777);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
        }
        catch (\Throwable $th) {
            // return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);

            if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/nomination_awards.csv";
            $file_link = env('APP_URL').$file_name;
            $columns = array('Campaign Name','Nominator First Name','Nominator Surname', 'Nominator Email','Nominee First Name','Nominee Surname','Nominee Email','Nominee Function','Nominee User Group','L1AdminFirst Name','L1Admin Surname','L1Admin Email','L1Admin User Group','Value Category Name','Level Name','Requested Value','Value','Reason For Nomination','Status','Reason For Decline','Created On','Updated On');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);

        }
    }

    public function awardCostCsv(Request $request){
        try{
            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';
            $group_id = array();
            if (!file_exists('reports')) {
                 File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/award-cost.csv";
            $file_link = env('APP_URL').$file_name;
            // $account_id = $request->account_id;
            $account_id = Helper::customDecrypt($request->account_id);
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        $group_id[] = $groups["user_group_id"];
                    }
                }
            }else{
                $group_id = array($request->user_group);
            }


            // PDF FILTER
            $awarded_data = $this->common_repository->getCommonNominationQuery(0,$group_id,$from, $to);
            $total_campaign_count = $awarded_data->count();
            $pdf_filter = $request->pdf_filter;
            if($pdf_filter == '1'){
                $pdf_filter = 'week';
                $coins_awarded = $awarded_data->select('*',DB::raw("CONCAT_WS('-',week(created_at),YEAR(created_at)) as weekyear"))->orderBy('created_at','ASC')->get()->groupBy("weekyear")->toArray();
            }
            else if($pdf_filter == '2'){
                $pdf_filter = 'month';
                $coins_awarded = $awarded_data->select('*',DB::raw("CONCAT_WS('-',month(created_at),YEAR(created_at)) as monthyear"))->orderBy('created_at','ASC')->get()->groupBy("monthyear")->toArray();
            }
            else if($pdf_filter == '0'){
                $pdf_filter = 'date';
                $coins_awarded = $awarded_data->select('*',DB::raw("date(created_at) as date"))->orderBy('created_at','ASC')->get()->groupBy("date")->toArray();
            }

            foreach($coins_awarded as $key => $value){
                $total = 0;
                $all_points = 0;
                foreach($value as $ky => $vl){
                    if(isset($new_array[$key][$vl["campaign_id"]]["total_points"])){
                        $total = $new_array[$key][$vl["campaign_id"]]["total_points"];
                        $new_array[$key][$vl["campaign_id"]]["total_points"] = $total+$vl["points"];
                    }
                    else{
                        $total = 0;
                        $new_array[$key][$vl["campaign_id"]]["total_points"] = $total+$vl["points"];
                        $total = $new_array[$key][$vl["campaign_id"]]["total_points"];
                    }
                    $all_points = $all_points+$vl["points"];
                }
                $new_array[$key]["all_points"] = $all_points;
            }

            $columns = array(ucfirst($pdf_filter),'Campaign Name','Total Points','Total %','Total Cost');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            foreach($new_array as $key => $value){

                if($pdf_filter == 'week'){
                    $date_array = explode("-",$key);
                    $dateTime = new DateTime();
                    $dateTime->setISODate($date_array[1], $date_array[0]);
                    $start= $dateTime->format('d-M-Y');
                    $dateTime->modify('+6 days');
                    $end = $dateTime->format('d-M-Y');
                    fputcsv($new_csv,array($start." To ".$end));
                }else if($pdf_filter == 'month'){
                    $month =  Carbon::createFromFormat('m-Y', $key)->format('M Y');
                    fputcsv($new_csv,array($month));
                }
                else{
                    fputcsv($new_csv,array($key));
                }
                if($request->campaign_id != '0' && !isset($value[$request->campaign_id]))
                {
                    $campaign_name = DB::table("value_sets")->select("name")->where("id",$request->campaign_id)->first();
                    fputcsv($new_csv,array("",$campaign_name->name,"0","0","0"));
                }
                foreach($value as $key1 => $value1){
                    if($key1 != "all_points"){
                        $campaign_name = DB::table("value_sets")->select("name")->where("id",$key1)->first();
                        if($value1["total_points"] == 0){
                            $percent = 0;
                        }
                        else{
                            $percent = round($value1["total_points"]*100/$value["all_points"],2);
                        }
                        $country_id = '228';
                        $currency_code = 'AED';
                        $currency_points = $this->common_repository->getAccountCurrencyPoints($country_id);
                        $data = $currency_code." ".round((float)$value1["total_points"]/ (float)$currency_points,2);
                        $from = date_create($request->from);
                        $to = date_create($request->to);
                        if($request->campaign_id != '0'){
                            if($key1 == $request->campaign_id){
                                fputcsv($new_csv,array("",$campaign_name->name,$value1["total_points"],$percent,$data));
                            }
                        }
                        else{
                            fputcsv($new_csv,array("",$campaign_name->name,$value1["total_points"],$percent,$data));
                        }
                    }
                }
            }
            fclose($new_csv);
            // chmod(public_path($file_name),0777);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
        }
        catch (\Throwable $th) {
            //  return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
             if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/award-cost.csv";
            $file_link = env('APP_URL').$file_name;
            $columns = array('Date','Campaign Name','Total Points','Total %','Total Cost');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            // chmod(public_path($file_name),0777);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);
        }

    }



    /**************************Nomination status csv******************/
    public function nominationStatusCsv(Request $request){
        try{
            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';
            $group_id = array();

            if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/nomination_status.csv";
            $file_link = env('APP_URL').$file_name;


            // $account_id = $request->account_id;
            $account_id = Helper::customDecrypt($request->account_id);

            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        $group_id[] = $groups["user_group_id"];
                    }
                }
            }else{
                $group_id = array($request->user_group);
            }
            $pdf_filter = $request->pdf_filter;

            // Total Nomination
            $nomination_total = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to]);

            // Pending Nomination
            $l1_pending = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->where('level_1_approval','0');
            $l2_pending = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->where('level_2_approval','0');

            // Approved Nomination
            $l1_decliend = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->where('level_1_approval','-1');
            $l2_decliend = UserNomination::whereIn('group_id',$group_id)->whereBetween('created_at', [$from, $to])->where('level_2_approval','-1');


            // Declined Nomination

            if($request->campaign_id != '0'){
                $nomination_total = $nomination_total->where('campaign_id',$request->campaign_id);
                $l1_pending = $l1_pending->where('campaign_id',$request->campaign_id);
                $l2_pending = $l2_pending->where('campaign_id',$request->campaign_id);

                $l1_decliend = $l1_decliend->where('campaign_id',$request->campaign_id);
                $l2_decliend = $l2_decliend->where('campaign_id',$request->campaign_id);


            }

            $total = $nomination_total->count();
            $total_l1_pending = $l1_pending->count();
            $total_l2_pending = $l2_pending->count();

            $total_l1_decliend = $l1_decliend->count();
            $total_l2_decliend = $l2_decliend->count();

            $total_l1_approved = $total-$total_l1_pending-$total_l1_decliend;
            $total_l2_approved = $total-$total_l2_pending-$total_l2_decliend;


            if($total !== 0 && $total_l1_approved !== 0){
                $l1_approvel_per = 100*$total_l1_approved/$total;
            }
            else{
                $l1_approvel_per = 0;
            }

            if($total !== 0 && $total_l2_approved !== 0){
                $l2_approvel_per = 100*$total_l2_approved/$total;
            }
            else{
                $l2_approvel_per = 0;
            }

            if($total !== 0 && $total_l1_decliend !== 0){
                $l1_decline_per = 100*$total_l1_decliend/$total;
            }
            else{
                $l1_decline_per = 0;
            }

            if($total !== 0 && $total_l2_decliend !== 0){
                $l2_decline_per = 100*$total_l2_decliend/$total;
            }
            else{
                $l2_decline_per = 0;
            }


            if($total !== 0 && $total_l1_pending !== 0){
                $l1_pending_per = 100*$total_l1_pending/$total;
            }
            else{
                $l1_pending_per = 0;
            }

            if($total !== 0 && $total_l2_pending !== 0){
                $l2_pending_per = 100*$total_l2_pending/$total;
            }
            else{
                $l2_pending_per = 0;
            }

            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, array("Status","Count (In number)","Count (In %age)"));
            fputcsv($new_csv, array("",""));

            fputcsv($new_csv, array("approved_l1",$total_l1_approved,round($l1_approvel_per,2)."%"));
            fputcsv($new_csv, array("approved_l2",$total_l2_approved,round($l2_approvel_per,2)."%"));
            fputcsv($new_csv, array("pending_l1",$total_l1_pending,round($l1_pending_per,2)."%"));
            fputcsv($new_csv, array("pending_l2", $total_l2_pending,round($l2_pending_per,2)."%"));
            fputcsv($new_csv, array("declined_l1",$total_l1_decliend,round($l1_decline_per,2)."%"));
            fputcsv($new_csv, array("declined_l2", $total_l2_decliend,round($l2_decline_per,2)."%"));


            fclose($new_csv);
            // chmod(public_path($file_name),0777);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
        }
        catch (\Throwable $th) {
            // return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);

            if (!file_exists('reports')) {
                File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/nomination_status.csv";
            $file_link = env('APP_URL').$file_name;
            $columns = array('Date','Campaign Name','Total Points','Total %','Total Cost');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            // chmod(public_path($file_name),0777);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);

        }
    }
    /**************************END Nomination status csv******************/


    /***********************
    fn to get rewards report
    ************************/
    public function rewardsReport(Request $request){
        try{

            $request['account_id'] =  Helper::customDecrypt($request->account_id);
            $rules = [
                'user_group' => 'required',
                'account_id' => 'required|integer|exists:accounts,id',
                'country_id' => 'required|integer|exists:countries,id',
                'from'      => 'required|date_format:Y-m-d',
                'to'      => 'required|date_format:Y-m-d',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            /*$country_logged_user = ProgramUsers::select('country_id')->where('account_id',$request->account_id)->first();
            $user_country = $country_logged_user->country_id;*/
            $user_country = $request->country_id;

            #Get_group_for_this_users
            $group_id = array();
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($request->account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        if(!in_array($groups['user_group_id'],$group_id)){
                            $group_id[] = $groups['user_group_id'];
                        }

                    }
                }
            }else{
                $group_id = array($request->user_group);
            }

            $data = $this->common_service->filterRewardsReport($request->all(),$group_id,$user_country);
            return response()->json(['message'=>'Data get successfully.', 'status'=>'success','data'=>$data]);exit;

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage(),'line'=>$th->getLine()]);
        }
    }/*********rewards_report_ends_here**********/


    //---------------  Product Order CSV Report ------------------

    public function productReportCsv(Request $request){
        try{
            $group_id = array();
            if (!file_exists('reports')) {
                 File::makeDirectory(public_path('reports'));
            }
            $file_name = "/reports/product-orders.csv";
            $file_link = env('APP_URL').$file_name;
            // $account_id = $request->account_id;
            $account_id = Helper::customDecrypt($request->account_id);
            $group_id = array();
            if($request->user_group == 0){
                $groupData = $this->common_repository->getUserGroups($account_id);
                if(!empty($groupData)){
                    foreach($groupData as $groups){
                        $group_id[] = $groups["user_group_id"];
                    }
                }
            }else{
                $group_id = array($request->user_group);
            }
            $account_ids = UsersGroupList::select('account_id')->whereIn('user_group_id',$group_id)->distinct()->get();

            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';
            $account_id = array();
            if(!empty($account_ids)){
                foreach($account_ids as $key=>$account){
                    $account_id[] = $account['account_id'];
                }
            }
            $pdf_filter = $request->pdf_filter;
            if($pdf_filter == '1'){
                $pdf_filter = "week";
                $order_data_pdf_filter = ProductOrder::select("*",DB::raw("CONCAT_WS('-',week(created_at),YEAR(created_at)) as weekyear"))->with(["product","denomination"])->whereIn('account_id',$account_id)->whereBetween('created_at', [$from, $to])->orderBy('created_at','asc')->get()->groupBy('weekyear')->toArray();
            }
            else if($pdf_filter == '2'){
                $pdf_filter = "month";
                $order_data_pdf_filter = ProductOrder::select("*",DB::raw("CONCAT_WS('-',MONTH(created_at),YEAR(created_at)) as monthyear"))->with(["product","denomination"])->whereIn('account_id',$account_id)->whereBetween('created_at', [$from, $to])->orderBy('created_at','asc')->get()->groupBy('monthyear')->toArray();
            }
            else if($pdf_filter == '0'){
                $pdf_filter = "date";
                $order_data_pdf_filter = ProductOrder::select("*",DB::raw("date(created_at) as date"))->with(["product","denomination"])->whereIn('account_id',$account_id)->whereBetween('created_at', [$from, $to])->orderBy('created_at','asc')->get()->groupBy("date")->toArray();
            }

            $columns = array(ucfirst($pdf_filter),'Product Name','Product Qty','Product Type','Order Status','Points','Name','Phone','Address','City','Country','Created Date');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            foreach($order_data_pdf_filter as $key => $value) {
                if($pdf_filter == 'week'){

                    $date_array = explode("-",$key);
                    $dateTime = new DateTime();
                    $dateTime->setISODate($date_array[1], $date_array[0]);
                    $start= $dateTime->format('d-M-Y');
                    $dateTime->modify('+6 days');
                    $end = $dateTime->format('d-M-Y');
                    fputcsv($new_csv,array($start." To ".$end));
                }else if($pdf_filter == 'month'){
                    $month =  Carbon::createFromFormat('m-Y', $key)->format('M Y');
                    fputcsv($new_csv,array($month));
                }
                else{
                    fputcsv($new_csv,array($key));
                }
                foreach($value as $key1 => $value1){
                    // Order Status
                    if($value1["status"] == 3){
                        $status = "Shipped";
                    }
                    else if($value1["status"] == 2){
                        $status = "Confirm";
                    }
                    else if($value1["status"] == 1){
                        $status = "Pending";
                    }
                    else if($value1["status"] == -1){
                        $status = "Cancel";
                    }
                    fputcsv($new_csv,array("",$value1["product"]["name"],$value1["quantity"],$value1["product"]["type"],$status,$value1["value"],$value1["first_name"]." ".$value1["last_name"],$value1["phone"],$value1["address"],$value1["city"],$value1["country"],$value1["created_at"]));
                }
            }

            fclose($new_csv);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
        }
        catch(\Throwable $th){
            // return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
            $columns = array(ucfirst($pdf_filter),'Product Name','Product Qty','Product Type','Order Status','Points','Name','Phone','Address','City','Country','Created Date');
            $file_name = "/reports/product-orders.csv";
            $file_link = env('APP_URL').$file_name;
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);
        }
    }

    public function productOverallReport(Request $request){
        try{
            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';
            $file_name = "/reports/product-overall-report.csv";
            $file_link = env('APP_URL').$file_name;

            $country_id = $request->country_id;

            $product = DB::table('products')
                        ->select("products_countries.*","products.*","product_catalogs.name as category_name","product_categories.name as sub_category_name")
                        ->leftJoin('products_countries', 'products.id', '=', 'products_countries.product_id')
                        ->leftJoin('product_catalogs', 'products.catalog_id', '=', 'product_catalogs.id')
                        ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
                        ->where("products_countries.country_id",$country_id)->get()->toArray();


            $columns = array("Date",'Product Name','Product Category','Product Sub Category','Product Viewed','Product Orderd');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);

            $from_date = date_create($request->from);
            $to_date = date_create($request->to);
            $date = date_format($from_date,'d M Y')." - ".date_format($to_date,'d M Y');
            foreach($product as $key => $value){
                $total_viewd = DB::table("products_accounts_seen")->where("product_id",$value->product_id)->whereBetween('created_at', [$from, $to])->count();
                $total_order = DB::table("product_orders")->where("product_id",$value->product_id)->whereBetween('created_at', [$from, $to])->count();

                fputcsv($new_csv,array($date,$value->name,$value->category_name,$value->sub_category_name,$total_viewd,$total_order));

            }
            fclose($new_csv);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
        }
        catch(\Throwable $th){
            // return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
            $columns = array("Date",'Product Name','Product Viewed','Product Orderd');
            $file_name = "/reports/product-orders.csv";
            $file_link = env('APP_URL').$file_name;
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);
        }

    }

    public function popularRewardCsv(Request $request){
        try{
            $from = $request['from'] . ' 00:00:01';
            $to = $request['to'] . ' 23:59:59';
            $file_name = "/reports/popular-award.csv";
            $file_link = env('APP_URL').$file_name;
            $country_id = $request->country_id;
            $popularRewards = DB::table('products as p')
                            ->select('p.id as productid', 'p.name as product_name','c.name as category_name', DB::Raw('COUNT(t.id) as view_count'),'o.order_count')
                            ->join(DB::raw('(SELECT o.product_id,o.created_at, count(distinct o.id) as order_count FROM product_orders as o GROUP BY o.product_id) as o'),'p.id','o.product_id')
                            ->leftjoin('products_accounts_seen as t', 'p.id', '=', 't.product_id')
                            ->leftjoin('products_countries as x', 'p.id', '=', 'x.product_id')
                            ->join('product_categories as c', 'c.id', '=', 'p.category_id')
                            //->where('product_orders.status', '=', 3)
                            ->where('o.created_at', '>=',$from)
                            ->where('o.created_at', '<=',$to)
                            ->where('x.country_id',$country_id)
                            ->groupBy('p.id','o.order_count')
                            ->orderBy('order_count','desc')
                            ->get();
            $columns = array("Date",'Product Name','Product Category','Product Orderd','Product View');
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            $from_date = date_create($request->from);
            $to_date = date_create($request->to);
            $date = date_format($from_date,'d M Y')." - ".date_format($to_date,'d M Y');
            foreach($popularRewards as $key => $value ){
                fputcsv($new_csv,array($date,$value->product_name,$value->category_name,$value->order_count,$value->view_count));
            }
            fclose($new_csv);
            return response()->json(['message' => 'success', 'status'=>'success', 'link' =>$file_link]);
        }
        catch(\Throwable $th){
            // return response()->json(['message' => 'Something get wrong! Please try again.', 'status'=>'error', 'errors' => $th->getMessage()]);
            $columns = array("Date",'Product Name','Product Viewed','Product Orderd');
            $file_name = "/reports/popular-award.csv";
            $file_link = env('APP_URL').$file_name;
            $new_csv = fopen(public_path($file_name) , 'w');
            fputcsv($new_csv, $columns);
            fclose($new_csv);
            return response()->json(['message' => 'No record found', 'status'=>'success', 'link' =>$file_link]);
        }
    }
}
