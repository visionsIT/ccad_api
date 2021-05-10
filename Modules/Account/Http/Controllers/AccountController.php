<?php namespace Modules\Account\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Account\Http\Requests\AccountPermissionsRequest;
use Modules\Account\Http\Requests\AccountRequest;
use Modules\Account\Http\Services\AccountService;
use Modules\Account\Models\Account;
use Modules\Account\Transformers\AccountTransformer;
use Modules\Nomination\Transformers\BadgesTransformer;
use Modules\Account\Transformers\AccountDataTransformer;
use Spatie\Fractal\Fractal;
use Modules\Nomination\Models\UserNomination;
use Modules\Nomination\Transformers\UserNominationTransformer;
use DB;
use Helper;

class AccountController extends Controller
{
    private $account_service;

    public function __construct(AccountService $account_service)
    {
        $this->account_service = $account_service;
		//$this->middleware('auth:api')->only(['getAuthenticatedAccountData','myAccountOrderData']);
		$this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Spatie\Fractal\Fractal
     */
    public function index(): Fractal
    {
        $accounts = $this->account_service->get();

        return fractal($accounts, new AccountTransformer);
    }

    /**
     * @param AccountRequest $request
     * @param Carbon $carbon
     *
     * @return Fractal
     */
    public function store(AccountRequest $request, Carbon $carbon): Fractal
    {
        $accounts = $this->account_service->store($request->all() + [ 'login_ip' => $request->ip() ], $carbon);

        return fractal($accounts, new AccountTransformer);
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
        $accounts = $this->account_service->show($id);

        return fractal($accounts, new AccountTransformer);
    }

    /**
     *
     * Update the specified resource in storage.
     *
     * @param AccountRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(AccountRequest $request, $id): JsonResponse
    {
        $this->account_service->update($request->all(), $id);

        return response()->json([ 'message' => 'Account Updated Successfully' ]);
    }

    /**
     *
     * Remove the specified resource from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->account_service->destroy($id);

        return response()->json([ 'message' => 'Account Trashed Successfully' ]);
    }


    /**
     * @param AccountPermissionsRequest $request
     * @param Account $account
     *
     * @return JsonResponse
     */
    public function syncPermissions(AccountPermissionsRequest $request, Account $account): JsonResponse
    {
        $this->account_service->syncPermissions($request->all(), $account);

        return response()->json([ 'message' => 'Permissions Assigned To Account Successfully' ]);
    }

    public function getAuthenticatedAccountData(Request $request)
    {
        return fractal($request->user(), new AccountTransformer);
    }

    public function getAuthenticatedAccountBudges(Request $request)
    {
        $account = $this->account_service->show($request->account_id);

        return $account->badges->map(function ($badge){
            return $badge->types->map(function ($type){
                return (new BadgesTransformer())->transform($type);
            });
        })->filter();


    }
    /******************
    My Account Data GET
    ******************/
    public function myAccountAllData(Request $request)
    {
        try{
            
            $rules = [
                'account_id' => 'required|integer|exists:accounts,id',
                'type' => 'required|integer',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $data = $this->account_service->filterAccountData($request->all());
            return fractal($data, new AccountDataTransformer);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
        
    }

    /******************
    My Activity Data GET
    ******************/
    /* public function myActivityAllData(Request $request)
    {
        $data = $request->all();
        try{
            
            $rules = [
                'account_id' => 'required|integer|exists:accounts,id',
                'campaign_id' => 'required|integer'
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $finalData = [];
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
            if($data['campaign_id'] == 0) { // for all campaign data

                $Campaigns_list = DB::table('value_sets')->select('id','name')->get();

                foreach($Campaigns_list as $campaign) {
                    $sentData = UserNomination::where(['account_id'=>$data['account_id'], 'campaign_id'=>$campaign->id])->with(['user_relation','user_account','type.valueset','user_ecard'])->orderBy('created_at','ASC')->get();
                    $receivedData = UserNomination::where(['user'=>$data['account_id'], 'campaign_id'=>$campaign->id])->with(['user_relation','user_account','type.valueset','user_ecard'])->where(function($q){
                            $q->where(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '1');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '1');
                            });
                        })->orderBy('created_at','ASC')->get();

                    if(!$sentData->isEmpty() || !$receivedData->isEmpty()) {
                        $finalData[] = [
                            'campaign_name' => $campaign->name,
                            'campaign_id' => $campaign->id,
                            'sent_data' => $sentData,
                            'received_data' => $receivedData,
                            'base_url' => $protocol.'://'.$_SERVER['HTTP_HOST']
                        ];
                    }
                }

            } else {   // for specific campaign
                $CampaignData = DB::table('value_sets')->select('name')->where('id', $data['campaign_id'])->first();
                $sentData = UserNomination::where(['account_id'=>$data['account_id'], 'campaign_id'=>$data['campaign_id']])->with(['user_relation','user_account','type.valueset','user_ecard'])->orderBy('created_at','ASC')->get();
                $receivedData = UserNomination::where(['user'=>$data['account_id'], 'campaign_id'=>$data['campaign_id']])->with(['user_relation','user_account','type.valueset','user_ecard'])->where(function($q){
                            $q->where(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '1');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '1');
                            });
                        })->orderBy('created_at','ASC')->get();
                if(!$sentData->isEmpty() || !$receivedData->isEmpty()) {
                    $finalData[] = [
                        'campaign_name' => $CampaignData->name,
                        'campaign_id' => $data['campaign_id'],
                        'sent_data' => $sentData,
                        'received_data' => $receivedData,
                        'base_url' => $protocol.'://'.$_SERVER['HTTP_HOST']
                    ];
                }
            }
            return response()->json(['data'=> $finalData,'message' => 'Data listed Successfully.', 'status' => 'success']);
            // return fractal($finalData, new UserNominationTransformer);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
        
    } */
    public function myActivityAllData(Request $request)
    {
        try{
            $request['account_id'] =  Helper::customDecrypt($request->account_id);
            $data = $request->all();
            
            $rules = [
                'account_id' => 'required|integer|exists:accounts,id',
                'campaign_id' => 'required|integer'
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            $finalData = [];
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
            if($data['campaign_id'] == 0) { // for all campaign data

                $Campaigns_list = DB::table('value_sets')->select('id','name')->get();

                foreach($Campaigns_list as $campaign) {
                    $sentData = UserNomination::where(['account_id'=>$data['account_id'], 'campaign_id'=>$campaign->id])->with(['user_relation','user_account','type.valueset','user_ecard'])->orderBy('created_at','ASC')->get();
                    $receivedData = UserNomination::where(['user'=>$data['account_id'], 'campaign_id'=>$campaign->id])->with(['user_relation','user_account','type.valueset','user_ecard'])->where(function($q){
                            $q->where(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '1');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '1');
                            });
                        })->orderBy('created_at','ASC')->get();

                    if(!$sentData->isEmpty() || !$receivedData->isEmpty()) {
                        $finalData[] = [
                            'campaign_name' => $campaign->name,
                            'campaign_id' => $campaign->id,
                            'sent_data' => $sentData,
                            'received_data' => $receivedData,
                            'base_url' => $protocol.'://'.$_SERVER['HTTP_HOST'],
							'certificate_image_dir' => "/uploaded/certificate_images/"
                        ];
                    }
                }

            } else {   // for specific campaign
                $CampaignData = DB::table('value_sets')->select('name')->where('id', $data['campaign_id'])->first();
                $sentData = UserNomination::where(['account_id'=>$data['account_id'], 'campaign_id'=>$data['campaign_id']])->with(['user_relation','user_account','type.valueset','user_ecard'])->orderBy('created_at','ASC')->get();
                $receivedData = UserNomination::where(['user'=>$data['account_id'], 'campaign_id'=>$data['campaign_id']])->with(['user_relation','user_account','type.valueset','user_ecard'])->where(function($q){
                            $q->where(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '1');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '2')
                                ->where('level_2_approval', '2');
                            })
                            ->orWhere(function($query){
                                $query->where('level_1_approval', '1')
                                ->where('level_2_approval', '1');
                            });
                        })->orderBy('created_at','ASC')->get();
                if(!$sentData->isEmpty() || !$receivedData->isEmpty()) {
                    $finalData[] = [
                        'campaign_name' => $CampaignData->name,
                        'campaign_id' => $data['campaign_id'],
                        'sent_data' => $sentData,
                        'received_data' => $receivedData,
                        'base_url' => $protocol.'://'.$_SERVER['HTTP_HOST'],
						'certificate_image_dir' => "/uploaded/certificate_images/"
                    ];
                }
            }
            return response()->json(['data'=> $finalData,'message' => 'Data listed Successfully.', 'status' => 'success']);
            // return fractal($finalData, new UserNominationTransformer);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something get wrong! Please try again.', 'errors' => $th->getMessage()], 402);
        }
        
    }

    public function fetchReportsAccess() {
        try {

            $response = response()->json(['status' => false, 'message' => 'no data found', 'data' => []]);

            $reports = DB::table('permissions')
            ->where('name', 'reports_emp_access')
            ->orWhere('name', 'reports_l1_access')
            ->orWhere('name', 'reports_l2_access')
            ->get();

            if ($reports && count($reports->toArray()) > 0) {
                $response = response()->json(['status' => true, 'message' => 'settings data', 'data' => $reports->toArray()]);
            }

            return $response;
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong! Please try again.', 'errors' => $th->getMessage(), 'line' => $th->getLine()], 402);
        }
    }

    public function reportsAccessUpdates(Request $request) {
        try {
            $input =  $request->all();
            $rules = [
                'name' => 'required',
                'status' => 'required|integer',
            ];
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails())
                return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

            DB::table('permissions')
            ->where('name', $request->name)
            ->update(['status' => $request->status]);

            return response()->json(['status' => true, 'message' => 'Setting has been updated successfully.']);

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Something went wrong! Please try again.', 'errors' => $th->getMessage(), 'line' => $th->getLine()], 402);
        }
    }

}
