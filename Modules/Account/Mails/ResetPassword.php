<?php namespace Modules\Account\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Account\Models\Account;
use Sichikawa\LaravelSendgridDriver\SendGrid;

class ResetPassword extends Mailable implements ShouldQueue
{
    use Queueable, SendGrid, SerializesModels;

    public $account, $password;

    /**
     * ResetPasswordToken constructor.
     *
     * @param Account $account
     * @param $token
     */
    public function __construct(Account $account, $password)
    {
        $this->password   = $password;
        $this->account = $account;
        $this->queue   = 'mails';
    }


    public function build(): void
    {
        //config('sendgrid.templates.' . "{$notifiable->communication_language}_confirm_reset_password_token")
        $this
            ->view([])
            ->to($this->account->email)
            ->from(config('sendgrid.emails.no-reply-email'))
            ->sendgrid([
                'content' => [
                    [
                        'type' => 'text/html',
                        'value'=> 'Hi '.$this->account->user->first_name.', <br><br> Here are the new credentials to access the system:<br><b>Email: </b>'.$this->account->email.'<br><b>Password:</b> '.$this->password
                    ],
                ],
            ]);
    }
}