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

        $subject ="Vodafone - Password Reset";

        // $message = 'Hi '.$account->user->first_name.', <br><br> Here are the new credentials to access the system:<br><b>Email: </b>'.$account->email.'<br><b>Password:</b> '.$password;
        $message = '<h2 style="font-size:20px;margin: 10px 0px 20px;">We heard you have forgotten your password for '. $account->user->username .'.</h2><p style="margin: 10px 0px; font-size: 16px;">Here are the new credentials to access the system: <br><b>Username:</b> '.$account->email.'<br><b>Password:</b> '.$password.'</p><p style="margin: 10px 0px; font-size: 16px;">If you continue to have problems logging into your account, please contact us at info@meriteincentives.com</p>';

        $email = $account->email;
        $token = $this->generateVerificationToken();
        Mail::send(new \Modules\Nomination\Mails\SendMail($email,$token,$message,$subject));

        // Mail::send(new \Modules\Account\Mails\ResetPassword($account, $password));

        // $this->tokens_repository->create([ 'account_id' => $account->id, 'token' => $token, 'type' => 0 ]);

        // $image_url = [
        //     'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
        //     'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
        //     'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
        //     'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
        // ];

        // Mail::send('emails.test', [ 'mes' => $message, 'image_url' => $image_url ], function ($m) use($account) {
        //     $m->to($account->email)->subject('Vodafone - Password Reset');
        // });
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
