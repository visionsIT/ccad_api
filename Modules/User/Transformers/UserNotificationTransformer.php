<?php

namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\UserNotifications;
use Modules\User\Models\ProgramUsers;
use Carbon\Carbon;
use DB;
class UserNotificationTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(UserNotifications $notification): array
    {

        if($notification->sender_account_id == Null){
            $sender = 'Admin';
            $senderEmail = 'Admin';
        }else{
            $sender = $notification->sender_account->name;
            $senderEmail = $notification->sender_account->email;
        }

        $user_data_program = ProgramUsers::where('account_id', $notification->receiver_account_id)->first();

        #get receiver time as per receiver country
        $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $notification->created_at);
        $country = DB::table('countries')->select('code')->where('id',$user_data_program->country_id)->first();
        $timezone =  \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country->code);
        if ($timezone) {
            $datetime->setTimezone($timezone[0]);
            $datetime = date('M d, Y h:i A', strtotime($datetime));
        }else{
            $datetime = date('M d, Y h:i A', strtotime($notification->created_at));
        }
        $type = 'order';
        if(!empty($notification->user_nomination)){
            if($notification->user_nomination->ecard_id == '' || $notification->user_nomination->ecard_id == Null){
                $type = 'nomination';
            }else{
                $type = 'ecard';
            }
        }

        return [
            'id' => $notification->id,
            'receiver' => $notification->receiver_account,
            //'sender_user_name' => $sender,
            //'sender_user_email' => $senderEmail,
            'label' => $notification->notification_type->name,
            'read_status' => $notification->read_status,
            'mail_content' => $notification->mail_content,
            'type'  =>  $type,
            'created_at' => date('M d, Y h:i A', strtotime($notification->created_at)), //April 15 2014 10:30pm
            'created_date_time'         => $datetime,
        ];


    }
}
