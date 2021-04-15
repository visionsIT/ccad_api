<?php


namespace Modules\User\Http\Services;

use Modules\User\Models\UserNotifications;


class UserNotificationService
{

	/************************
	fn to save notifications
	************************/
    public function creat_notification($receiver_account_id, $sender_account_id, $user_nomination_id, $user_order_id, $notification_type_id, $mail_content ) {
        $date = date('Y-m-d h:i:s');
	    return UserNotifications::insert([
	            'receiver_account_id' => $receiver_account_id, 
	            'sender_account_id' => $sender_account_id, 
	            'user_nomination_id' => $user_nomination_id, 
	            'user_order_id' => $user_order_id,
	            'notification_type_id' => $notification_type_id,
	            'mail_content' => $mail_content,
	            'created_at'=>$date,
	            'updated_at'=>$date
	        ]);
    }/******fn ends****/
}
