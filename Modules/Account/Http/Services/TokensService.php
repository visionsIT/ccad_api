<?php namespace Modules\Account\Http\Services;

use Illuminate\Support\Facades\Mail;
use Modules\Account\Repositories\TokensRepository;

/**
 * Class TokenService
 *
 * @package Modules\Account\Service
 */
final class TokensService
{
    private $tokens_repository;

    public function __construct(TokensRepository $tokens_repository)
    {
        $this->tokens_repository = $tokens_repository;
    }

    /**
     *
     * @return string|null
     * @throws \Exception
     */
    public function generateVerificationToken(): ?string
    {
        $token = str_random(50);

        if ($this->tokens_repository->getCount('token', $token) === 0) {
            return $token;
        }

        return $this->generateVerificationToken();
    }

    /**
     * @param $account
     * @param string $full_name
     *
     * @throws \Exception
     */
    public function sendUniqueVerificationCodeToAccount($account, $full_name = 'Special User'): void
    {
        $token = $this->generateVerificationToken();

        $this->tokens_repository->create([ 'account_id' => $account->id, 'token' => $token, 'type' => 1 ]);

        $account->notify(new VerificationCode($account, [ 'token' => $token, 'name' => $full_name ]));
    }

    /**
     * @param $account
     *
     * @throws \Exception
     */
    public function sendResetPasswordCodeToAccount($account,$password): void
    {
        // Generate verification_token or code depends on user agent then update it in account table
        // $token = $this->generateVerificationToken();

        $subject ="Kafu by AD Ports - Password Reset";

        $message = 'Hi '.$account->user->first_name.', <br><br> Here are the new credentials to access the system:<br><b>Email: </b>'.$account->email.'<br><b>Password:</b> '.$password;
        $email = $account->email;
        $token = $this->generateVerificationToken();
        Mail::send(new \Modules\Nomination\Mails\SendMail($email,$token,$message,$subject));
        // Mail::send(new \Modules\Account\Mails\ResetPassword($account, $password));

        // $this->tokens_repository->create([ 'account_id' => $account->id, 'token' => $token, 'type' => 0 ]);
    }

    public function findToken($token, $type)
    {
        return $this->tokens_repository->findToken($token, $type);
    }

    /**
     * @return int
     */
    public function deleteExpiredTokens(): int
    {
        return $this->tokens_repository->deleteExpiredTokens();
    }

    /**
     * @param $account_id
     * @param $type
     *
     * @return int
     */
    public function deleteColumnViaAccountId($account_id, $type): int
    {
        return $this->tokens_repository->deleteColumnViaAccountId($account_id, $type);
    }

}
