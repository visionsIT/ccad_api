<?php namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Account\Http\Requests\ChangeOldPasswordRequest;
use Modules\Account\Http\Requests\CreatePasswordRequest;
use Modules\Account\Http\Requests\ResetPasswordRequest;
use Modules\Account\Http\Services\PasswordsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

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
    public function __construct(PasswordsService $password_service)
    {
        $this->password_service = $password_service;
    }

    /**
     * @param ResetPasswordRequest $request
     *
     * @return Response
     * @throws \Exception
     */
    public function resetPassword(ResetPasswordRequest $request): Response
    {
        $this->password_service->resetPassword($request->email);

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
