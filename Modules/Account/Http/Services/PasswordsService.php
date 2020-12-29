<?php namespace Modules\Account\Http\Services;

use Illuminate\Support\Str;
use Modules\Account\Http\Repositories\AccountRepository;
use Illuminate\Support\Facades\Hash;

/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class PasswordsService
{
    protected $account_repository, $token_service;

    /**
     * PasswordsService constructor.
     *
     * @param AccountRepository $account_repository
     * @param TokensService $token_service
     */
    public function __construct(AccountRepository $account_repository, TokensService $token_service)
    {
        $this->account_repository = $account_repository;
        $this->token_service      = $token_service;
    }

    /**
     * @param $email
     *
     * @throws \Exception
     */
    public function resetPassword($email): void
    {
        $account = $this->account_repository->findAccountByEmail($email);
        // $password = Str::random(8);
        // $account->password = $password;
        // $account->update();
        // $this->token_service->sendResetPasswordCodeToAccount($account,$password);
        $this->token_service->sendResetPasswordLink($account);
    }

    /**
     * @param $token
     *
     * @return mixed
     */
    public function confirmResetPassword($token)
    {
        //delete expired tokens
        $this->token_service->deleteExpiredTokens();

        //check if the token in url exists in reset_passwords table
        return $this->token_service->findToken($token, 0);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function createNewPassword($data): bool
    {
        if ($info = $this->token_service->findToken($data['token'], 0)) {

            $this->account_repository->update([ 'password' => $data['password'] ], $info->account_id);

            // update user verification token to null
            $this->token_service->deleteColumnViaAccountId($info->account_id, 0);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param $data
     * @param $id
     */
    public function changeOldPassword($id, $data): void
    {
        $this->account_repository->update([ 'password' => $data['password'] ], $id);
    }

}
