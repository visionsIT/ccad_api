<?php namespace Modules\Nomination\Mails;

use Illuminate\Mail\Mailable;
use Modules\Account\Models\Account;
use Sichikawa\LaravelSendgridDriver\SendGrid;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use SendGrid, SerializesModels;

    public $account, $token, $message, $subject, $email;

    /**
     * SendMail constructor.
     * @param $email
     * @param $token
     * @param $message
     * @param $subject
     */
    public function __construct($email, $token,$message,$subject)
    {
        $this->token   = $token;
        $this->email = $email;
        $this->message = $message;
        $this->subject = $subject;
    }


    /**
     *
     */
    public function build(): void
    {
        $image_url = [
            'blue_logo_img_url' => env('APP_URL')."/img/".env('BLUE_LOGO_IMG_URL'),
            'smile_img_url' => env('APP_URL')."/img/".env('SMILE_IMG_URL'),
            'blue_curve_img_url' => env('APP_URL')."/img/".env('BLUE_CURVE_IMG_URL'),
            'white_logo_img_url' => env('APP_URL')."/img/".env('WHITE_LOGO_IMG_URL'),
        ];     
        $this->view('emails.test')
            ->to($this->email)
            ->subject($this->subject)
            ->from(config('sendgrid.emails.no-reply-email'))
            ->with([ 'mes' => $this->message, 'image_url' => $image_url ])
            ->sendgrid([
                'template_id' => config('sendgrid.templates._reset_password_code'),
            ]);
    }
}
