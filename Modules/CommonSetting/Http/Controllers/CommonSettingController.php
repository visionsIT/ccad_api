<?php namespace Modules\CommonSetting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Program\Models\Currency;
use Modules\CommonSetting\Models\PointRateSettings;
use Validator;
use Spatie\Permission\Models\Role;
use DB;

class CommonSettingController extends Controller
{
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

               // echo "<pre>";print_r($get_data);die;

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
            $currency_list = Currency::all();
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
                    'points' => 'required|integer||min:1',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails())
                    return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                if(isset($request->status) && $request->status != ''){
                    $update_array = array('points'=>$request->points,'status'=>$request->status);
                }else{
                    $update_array =  array('points'=>$request->points);
                }

                $update = PointRateSettings::where('id',$id)->update($update_array);

                if($update){
                    return response()->json(['message'=>'Updated successfully.', 'status'=>'success']);exit;;
                }else{
                    return response()->json(['message'=>"Check id and try again.", 'status'=>'error']);
                }
            }else{

                $rules = [
                    'currency_id' => 'required|unique:point_rate_settings,currency_id|integer|exists:currencies,id',
                    'points' => 'required|integer||min:1',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails())
                    return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

                $PointRateSettings = new PointRateSettings;
                $PointRateSettings->currency_id = $request->currency_id;
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
            $currency_pts_list = PointRateSettings::with(['currency'])->get();
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
            'currency_id' => 'required|integer|exists:point_rate_settings,currency_id',
            'amount' => 'required|numeric',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        
        try{
            $get_currency_point = PointRateSettings::select('points','status')->where('currency_id','=',$request->currency_id)->first();

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
            'currency_id' => 'required|integer|exists:point_rate_settings,currency_id',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        try{

            PointRateSettings::where('default_currency','=','1')->update(['default_currency'=>'0']);

            $check_status = PointRateSettings::select('status')->where('currency_id','=',$request->currency_id)->first();

            if($check_status->status == '1'){
                PointRateSettings::where('currency_id','=',$request->currency_id)->update(['default_currency'=>'1']);

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
}