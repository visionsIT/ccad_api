<?php namespace Modules\Program\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Program\Http\Requests\ProgramRequest;
use Modules\Program\Http\Services\ProgramService;
use Modules\Program\Transformers\ProgramsTransformer;
use Modules\Program\Transformers\VouchersTransformer;
use Modules\Program\Models\Voucher;
use Modules\Program\Models\Ecards;
use Modules\Program\Models\UserVouchers;
use Modules\User\Http\Services\UserService;
use Modules\User\Models\UsersPoint;
use Modules\User\Transformers\UserTransformer;
use Modules\Program\Transformers\EcardsTransformer;
use Modules\Program\Models\UsersEcards;
use Modules\User\Models\ProgramUsers;
use Illuminate\Support\Facades\Mail;
use File;
use Spatie\Fractal\Fractal;
use Spatie\Browsershot\Browsershot;
use Helper;

class ProgramController extends Controller
{
    private $program_service;
    private $service;

    public function __construct(ProgramService $program_service, UserService $service)
    {
        $this->program_service = $program_service;
        $this->service = $service;
		$this->middleware('auth:api');
        // $this->middleware('auth:api', ['sendEcard']);
        // $this->middleware('auth:api')->only(['sendEcard']);
        // $this->middleware('guest');
        // $this->middleware('auth');
        // $this->middleware('signed');
        // $this->middleware('throttle:6,1')->only('verify', 'resend');
    }



    /**
     * @param Request $request
     *
     * @return Fractal
     */
    public function index(Request $request): Fractal
    {
        $programs = $this->program_service->get(30, $request->query());

        return fractal($programs, new ProgramsTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProgramRequest $request
     *
     * @return mixed
     */
    public function store(ProgramRequest $request)
    {
        $program = $this->program_service->store($request->all());

        return fractal($program, new ProgramsTransformer());
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
        $program = $this->program_service->find($id);

        return fractal($program, new ProgramsTransformer());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProgramRequest $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function update(ProgramRequest $request, $id): JsonResponse
    {
        $this->program_service->update($request->all(), $id);

        return response()->json([ 'message' => 'Program Updated Successfully' ]);
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
        $this->program_service->destroy($id);

        return response()->json([ 'message' => 'Program Trashed Successfully' ]);
    }

    /**
     * @param Request $request
     *
     *` @return Fractal
     */
    public function search(Request $request): Fractal
    {
        $users = $this->service->search($request);

        return fractal($users, new UserTransformer());
    }

    public function addVoucher(Request $request)
    {

        $rules = [
            'voucher_name'    => 'required|unique:vouchers,voucher_name',
            'voucher_points'    => 'required|integer',
            'start_datetime'    => 'required',
            'end_datetime'    => 'required',
            'timezone'    => 'required',
            'quantity'    => 'required|integer',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $voucherDetails = Voucher::create([
            'voucher_name'    => $request->voucher_name,
            'voucher_points'    => $request->voucher_points,
            'start_datetime'    => $request->start_datetime,
            'end_datetime'    => $request->end_datetime,
            'timezone'    => $request->timezone,
            'quantity'    => $request->quantity,
            'description'    => ($request->description && $request->description != '')?$request->description:null,
        ]);
       return response()->json(['voucherDetails'=>$voucherDetails, 'status'=>'success']);
    }

    public function getVouchers(Request $request): Fractal
    {
        $data = [
            'searchText' => ($request->q)?$request->q:'',
            'orderBy' => ($request->order)?$request->order:'DESC',
            'col' => ($request->col)?$request->col:'id'
        ];

        $voucherDetails = Voucher::getVouchers($data);
        return fractal($voucherDetails, new VouchersTransformer());
    }

    public function updateVoucherStatus(Request $request)
    {
        $rules = [
            'id'    => 'required|integer',
            'status'    => 'required|integer',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        $voucher = Voucher::find($request->id);
        if($voucher != ''){
            $voucher->status = $request->status;
            $voucher->save();
            return response()->json(['message'=>'Voucher status changed successfully. ', 'status'=>'success']);
        }
        return response()->json(['message'=>"Voucher ID doesn't exist", 'status'=>'error']);

    }

    public function updateVoucherDetails(Request $request, $id): Fractal
    {
        $rules = [
            'voucher_name' => 'required:vouchers,voucher_name,'.$id,
            'voucher_points'    => 'required|integer',
            'start_datetime'    => 'required',
            'end_datetime'    => 'required',
            'timezone'    => 'required',
            'quantity'    => 'required|integer',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $voucherDetail = Voucher::find($id);
        $voucherDetail->voucher_name = $request->voucher_name;
        $voucherDetail->voucher_points    = $request->voucher_points;
        $voucherDetail->start_datetime    = $request->start_datetime;
        $voucherDetail->end_datetime    = $request->end_datetime;
        $voucherDetail->timezone    = $request->timezone;
        $voucherDetail->quantity    = $request->quantity;
        $voucherDetail->description    = ($request->description && $request->description != '')?$request->description:null;
        $voucherDetail->save();
        return fractal($voucherDetail, new VouchersTransformer());
    }

    public function redeemVoucher(Request $request) {
        $rules = [
            'voucher'    => 'required',
            'timezone'   => 'required',
            'local_time' => 'required',
            'account_id' => 'required',
            'user_id'    => 'required'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $input = $request->all();

        $timestamp = \Carbon\Carbon::parse($input['local_time'])->timestamp;
        $voucher_use = Voucher::where('voucher_name',$input["voucher"])->first();
        if($voucher_use) {
            $voucherStartStamp = \Carbon\Carbon::parse($voucher_use->start_datetime)->timestamp;
            $voucherEndStamp = \Carbon\Carbon::parse($voucher_use->end_datetime)->timestamp;
            if($timestamp >= $voucherStartStamp && $timestamp <= $voucherEndStamp) {
                if($voucher_use->quantity == $voucher_use->used_count) {
                    return response()->json(['status' => true, 'message'=>'Voucher not available']);
                } /*else if($voucher_use->timezone != $input['timezone']) {
                    return response()->json(['status' => true, 'message'=>'Voucher not available in your locale.']);
                }*/ else {
                    $account_id = Helper::customDecrypt($input['account_id']);
                    $user_id = Helper::customDecrypt($input['user_id']);
                    $check = UserVouchers::where('voucher_id', $voucher_use->id)->where('account_id', $input['account_id'])->first();

                    if($check) {
                        return response()->json(['status' => true, 'message'=>'Voucher already used']);
                    } else {
                        UserVouchers::create([
                            'account_id'    => $input['account_id'],
                            'voucher_id'    => $voucher_use->id,
                            'voucher_points'    => $voucher_use->voucher_points,
                            'timezone'    => $voucher_use->timezone
                        ]);

                        $points = UsersPoint::where('user_id', $input['user_id'])->orderBy('id', 'desc')->first();

                        if($points) {
                            $newPointBalance = $points->balance + $voucher_use->voucher_points;
                        } else {
                            $newPointBalance = $voucher_use->voucher_points;
                        }

                        UsersPoint::create([
                            'user_id'    => $input['user_id'],
                            'value'    => $voucher_use->voucher_points,
                            'transaction_type_id'    => 5,
                            'balance'    => $newPointBalance,
                            'created_by_id' => $input['account_id']
                        ]);

                        $voucherDetail = Voucher::find($voucher_use->id);
                        $voucherDetail->used_count = (int)$voucher_use->used_count + 1;

                        $voucherDetail->save();

                        return response()->json(['status' => true, 'message'=>'Voucher applied']);
                    }
                }
            } else {
                return response()->json(['status' => true, 'message'=>'Voucher expired']);
            }
        } else {
            return response()->json(['status' => false, 'message'=>'Voucher Not Found.']);
        }
    }

    public function getVoucherUsers(Request $request, $id) {
        $voucher_users = UserVouchers::join('program_users', 'user_vouchers.account_id', '=', 'program_users.account_id')
            ->join('vouchers', 'user_vouchers.voucher_id', '=', 'vouchers.id')
            ->select('program_users.first_name', 'program_users.last_name', 'program_users.email', 'program_users.title', 'vouchers.voucher_name', 'vouchers.voucher_points', 'user_vouchers.created_at as voucher_used_date')
            ->where('user_vouchers.voucher_id',$id)
            ->orderBy('user_vouchers.created_at','desc')
            ->get();
        if($voucher_users) {
            return response()->json(['data'=>$voucher_users, 'message'=>'Data listed successfully.', 'status'=>'success']);
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }
    }

    public function createEcards(Request $request) {

        try {
            $rules = [
                'card_title'    => 'required|unique:ecards,card_title',
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
                $imgName = 'e_card'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
                $imgName = str_replace(" ","_",$imgName);
                $destinationPath = public_path('uploaded/e_card_images/');
                $file->move($destinationPath, $imgName);
            }
            $newCard = Ecards::create([
                'card_title' => $request->card_title,
                'card_image' => $imgName,
                'allow_points' => $request->points_allowed
            ]);
            return response()->json(['status' => 'true', 'message' => 'Occassion card created successfully.', 'data' => $newCard]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'true', 'message'=>'Something went wrong! Please try after some time.']);
        }
    }

    /**
     * @param Request $request
     *
     * @return Fractal
     */
    public function getEcards(Request $request): Fractal
    {
        try{
            if( $request->get('p') == 'front'){
                $eCards = Ecards::orderBy('id', 'desc')->get();
            }else{
                $eCards = Ecards::orderBy('id', 'desc')->paginate(10);
            }

            return fractal($eCards, new EcardsTransformer());
        }catch (\Throwable $th) {
            return response()->json(['status' => true, 'message'=>'Something went wrong! Please try after some time.']);
        }

    }

    /**
     * get specific card details
     *
     * @param $id
     *
     * @return Fractal
     */
    public function getCardDetails($id): Fractal
    {
        $eCard = Ecards::find($id);

        return fractal($eCard, new EcardsTransformer());
    }

    public function updateEcardDetails(Request $request, $id): Fractal
    {
        $rules = [
            'card_title' => 'required:ecards,card_title,'.$id,
            'points_allowed'    => 'required|integer',
            'id' => 'required|integer|exists:ecards,id',
        ];
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $eCardDetails = Ecards::find($id);
        $imgName = $eCardDetails->card_image;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_name = $file->getClientOriginalName();
            $file_ext = $file->getClientOriginalExtension();
            $fileInfo = pathinfo($file_name);
            $filename = $fileInfo['filename'];
            $imgName = 'e_card'.$filename.substr(strftime("%Y", time()),2).'.'.$file_ext;
            $imgName = str_replace(" ","_",$imgName);
            $destinationPath = public_path('uploaded/e_card_images/');
            $file->move($destinationPath, $imgName);
        }

        $eCardDetails->card_title = $request->card_title;
        $eCardDetails->card_image = $imgName;
        $eCardDetails->allow_points = $request->points_allowed;
        $eCardDetails->save();
        return fractal($eCardDetails, new EcardsTransformer());
    }

    public function sendEcard(Request $request)
    {

        $rules = [
            'sender_id' => 'required|integer',
            'send_to_id' => 'required|integer',
            'image_message' => 'required',
            'ecard_id' => 'required|integer|exists:ecards,id',
            'send_type' => 'required'
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);

        $sent = UsersEcards::create([
            'ecard_id' => $request->ecard_id,
            'sent_to' => $request->send_to_id,
            'image_message' => $request->image_message,
            'sent_by' => $request->sender_id,
            'points' => $request->points,
            'send_type' => $request->send_type,
            'send_datetime' => $request->send_datetime,
            'send_timezone' => $request->send_timezone,
        ]);
        if($sent) {
            $image_url = [
                'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
                'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
                'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
                'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
            ];

            $eCardDetails = Ecards::find($request->ecard_id);
            $sendToUser = ProgramUsers::find($request->send_to_id);
            $senderUser = ProgramUsers::find($request->sender_id);

            $path = public_path().'/uploaded/e_card_images/new';
            if(!File::exists($path)) {
                File::makeDirectory($path, $mode = 0777, true, true);
            }
            $randm = rand(100,1000000);
            $newImage = $randm.time().'-'.$eCardDetails->card_image;
            $file_path = "/uploaded/e_card_images/new";

            $prev_img = '/uploaded/e_card_images/'.$eCardDetails->card_image;
            $prev_img_path = url($prev_img);

            $update = UsersEcards::where('id',$sent->id)->update(['new_image'=>$newImage,'image_path'=>$file_path]);

            if($update === 1){
                $destinationPath = public_path('uploaded/e_card_images/new/'.$newImage);

                $image_mesaage = str_replace(" ","%20",$request->image_message);#bcs_send_in_url
                $destinationPath = public_path('uploaded/e_card_images/new/'.$newImage);
                $conv = new \Anam\PhantomMagick\Converter();
                $options = [
                    'width' => 640,'quality' => 90
                ];
               // $imageNAme = 'ripple_e_cardVodafone_Congrats_ecards20.jpg';
                $conv->source(url('/newImage/'.$eCardDetails->card_image.'/'.$image_mesaage))
                    ->toPng($options)
                    ->save($destinationPath);
            }

            $new_img = '/uploaded/e_card_images/new/'.$newImage;
            $new_img_path = url($new_img);

            $data = [
                'email' => $sendToUser->email,
                'username' => $sendToUser->first_name.' '. $sendToUser->last_name,
                'card_title' => $eCardDetails->card_title,
                'sendername' => $senderUser->first_name.' '. $senderUser->last_name,
                'image' => $eCardDetails->card_image,
                'image_message' => $request->image_message,
                'new_image' => $newImage,
                'file_path' => $file_path,
                'full_img_path' => $new_img_path
            ];



            try {
                // Mail::send('emails.sendEcard', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
                //     $m->to($data["email"])->subject($data["card_title"].' Ecard!');
                // });
                return response()->json(['message'=>'Ecard sent successfully. ', 'status'=>'success','data'=>$data]);
            } catch (\Exception $e) {
                UsersEcards::where($sent->id)->delete();
                return response()->json(['message'=>$e->getMessage(), 'status'=>'error']);
            }
        } else {
            return response()->json(['message'=>"Something went wrong! Please try after some time.", 'status'=>'error']);
        }

    }

    public function updateEcardStatus(Request $request)
    {
        $rules = [
            'id'    => 'required|integer',
            'status'    => 'required|integer',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        $eCard = Ecards::find($request->id);
        if($eCard != ''){
            $eCard->status = $request->status;
            $eCard->save();
            return response()->json(['message'=>'Status changed successfully. ', 'status'=>'success']);
        }
        return response()->json(['message'=>"This card doesn't exist", 'status'=>'error']);

    }

}
