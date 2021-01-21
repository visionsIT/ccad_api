<?php
namespace App\Http\Controllers;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Laravel\Passport\Http\Controllers\AccessTokenController as AccessTokenController;
use Modules\Account\Models\Account;
use DB;
use Illuminate\Support\Facades\Mail;

class AuthController extends AccessTokenController
{
    use AuthenticatesUsers;

    //custom login method
    public function login(Request $request){
    	try{

    		$account = Account::where('email', $request->email)->first();

	        if(!empty($account)){

	        	if($account->login_attempts >= 3){
		        	return response()->json(["error"=> "account_blocked","error_description"=> "The user account has been blocked.","message"=> "The user account has been blocked."]);
		        }else{
                    DB::table('oauth_access_tokens')->where(['user_id'=>$account->id, 'revoked'=> '0'])->update(['revoked'=>'1']);

                    if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password, 'status' => '1'])){

		        		Account::where('email',$request->email)->update(['login_attempts'=>0]);

			            $roleInfo =  DB::table('model_has_roles')->select('roles.*')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')->where(['model_has_roles.model_id' => $account->id])->get()->toArray();

			            $userInfo = DB::table('program_users')->where('account_id', $account->id)->first();
			            if(count($roleInfo)>0 && $roleInfo[0]->general_permission == 0 && $userInfo->id != $userInfo->vp_emp_number ){
			            	return response()->json(["error"=> "login_not_allowed","error_description"=> "Login not allowed","message"=> "Login not allowed for this user."]);exit;
			            } else {
			                $successToken =  $account->createToken('userToken'.$account->id)->accessToken;

		    				return response()->json(["token_type"=> "Bearer","access_token"=> $successToken]);
			                exit;
			            }
				    }else{ #end_if_attempt

			        	$increase_attempt = $account->login_attempts + 1;
			        	Account::where('email',$request->email)->update(['login_attempts'=>$increase_attempt]);

			        	#if_user_attempt_wrong_password_3rd_time_then_send_mail_about_block
			        	if($increase_attempt >= 3){
			        		$image_url = [
					            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
					            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
					            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
					            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
					        ];

					        #Send_Email_to_User
					        $data = [
					            'email' => $request->email,
					            'name' => $account->name,
					        ];

				            Mail::send('emails.UserBlockMail', ['data' => $data, 'image_url'=>$image_url], function ($m) use($data) {
				                $m->to($data["email"])->subject('Account Blocked');
				            });

				            #Send_Email_to_Admin
					        $data1 = [
					            'email' => env('MAIL_SEND_TO'),
					            'name' => $account->name,
					            'user_email' => $account->email
					        ];

				            Mail::send('emails.UserBlockMailAdmin', ['data' => $data1, 'image_url'=>$image_url], function ($m) use($data1) {
				                $m->to($data1["email"])->subject('Account Blocked');
				            });

				            return response()->json(["error"=> "account_blocked","error_description"=> "The user account has been blocked.","message"=> "The user account has been blocked."]);

			        	}else{
			        		return response()->json(["error"=> "invalid_credentials","error_description"=> "The user credentials were incorrect.","message"=> "The user credentials were incorrect."]);exit;
			        	}

				    }

		        }

	    	} else { #end_if_account_not_empty
	        	return response()->json(["error"=> "invalid_credentials","error_description"=> "The user email was incorrect.","message"=> "The email does not exists in database."]);exit;
	        }

		}catch(\Throwable $th){
            return response()->json([
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile()
            ]);
        }


    }/****login fn ends****/

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

}
