<?php namespace Modules\Account\Http\Controllers;

use Modules\Account\Http\Requests\ChangeOldPasswordRequest;
use Modules\Account\Http\Requests\CreatePasswordRequest;
use Modules\Account\Http\Requests\ResetPasswordRequest;
use Modules\Account\Http\Services\PasswordsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Account\Repositories\AccountRepository;
use Modules\User\Models\UsersGroupList;

/**
 * Class PasswordController
 *
 * @package App\Components\Account\Controllers
 */
class PasswordsController extends Controller
{
    private $password_service;

    /**
     * PasswordsController constructor.
     *
     * @param PasswordsService $password_service
     */
    public function __construct(PasswordsService $password_service,AccountRepository $account_repository)
    {
        $this->password_service = $password_service;
        $this->account_repository = $account_repository;
    }

    /**
     * @param ResetPasswordRequest $request
     *
     * @return Response
     * @throws \Exception
     */
    public function resetPassword(ResetPasswordRequest $request,$status = null): Response
    {

        $account = $this->account_repository->findAccountByEmail($request->email);

        if($account->login_attempts >= 3){
            return response(['message'=>'Sorry, your account is blocked. Please contact your program manager.','status'=>'error']);
        }

        if($status == 0){ //admin
            $check_admin = UsersGroupList::where('account_id',$account->id)->where('user_role_id',4)->first();

            if(empty($check_admin)){
                return response(['message'=>'This is not an admin user.','status'=>'error']);
            }
        }else{
            //users
            $check_admin = UsersGroupList::where('account_id',$account->id)->where('user_role_id','!=',4)->first();

            if(empty($check_admin)){
                return response(['message'=>'This is not a Frontend user.','status'=>'error']);
            }
        }

        $this->password_service->resetPassword($request->email,$status);

        return response([ 'message' => 'The token has been sent to your mail successfully' ]);
    }

    /**
     * @param $token
     *
     * @POST("password/reset/{token}")
     *
     * @return mixed
     */
    public function confirmResetPassword($token)
    {
        if ($this->password_service->confirmResetPassword($token)) {
            return response([ 'message' => __('common.success-render-msg') ]);
        }

        return response([ 'message' => __('common.error-msg') ], 400);
    }

    /**
     * @param CreatePasswordRequest $request
     *
     * @return Response
     */
    public function createNewPassword(CreatePasswordRequest $request): Response
    {
        if ($this->password_service->createNewPassword($request->all())) {
            return response([ 'message' => __('common.success-success-msg') ]);
        }

        return response([ 'message' => __('common.error-msg') ], 400);
    }

    /**
     * @param ChangeOldPasswordRequest $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function changeOldPassword(ChangeOldPasswordRequest $request, $id)
    {
        $this->password_service->changeOldPassword($id, $request->all());

        return response([ 'message' => __('The password has changed successfully') ]);
    }

}
