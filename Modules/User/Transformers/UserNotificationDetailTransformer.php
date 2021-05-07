<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\UserNomination;
use Modules\User\Models\UserNotifications;
use Modules\Account\Models\Account;
use Modules\User\Models\ProgramUsers;
use Modules\Program\Models\UsersEcards;
use DB;
use Carbon\Carbon;
use Helper;
class UserNotificationDetailTransformer extends TransformerAbstract
{
    /**
     * @param UserNomination $model
     *
     * @return array
     */
    public function transform(UserNotifications $model): array
    {
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/user_nomination_files/';

        if($model->user_order_id == null || $model->user_order_id == ''){
            //nomination
            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
            $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/user_nomination_files/';
            $eCardimgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/e_card_images/new/';

            $nominated_user = ProgramUsers::where('account_id',$model->user_nomination->user)->first();

            //$wall_setting = $model->campaign->value_set_relation->wall_setings['wall_settings'];//for_wall_sett

            if($model->user_nomination->ecard_id == '' || $model->user_nomination->ecard_id == Null){
                $ecard_image = '';
                $type = 'nomination';
            }else{
                $UsersEcards = UsersEcards::select('new_image','image_path')->where('id',$model->user_nomination->ecard_id)->first();
                $ecard_image = $eCardimgUrl.$UsersEcards->new_image;
                $type = 'ecard';
            }

            $user_data_program = ProgramUsers::where('account_id', $model->receiver_account_id)->first();
            #get receiver time as per receiver country
            $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $model->created_at);
            $country = DB::table('countries')->select('code')->where('id',$user_data_program->country_id)->first();
            $timezone =  \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country->code);
            if ($timezone) {
                $datetime->setTimezone($timezone[0]);
                $datetime = date('M d, Y h:i A', strtotime($datetime));
            }else{
                $datetime = date('M d, Y h:i A', strtotime($model->created_at));
            }

            return [
                'id'                        =>  Helper::customCrypt($model->id),
                'campaign_id'               => $model->user_nomination->campaignid,
                //'submission_id'             => $model->user_nomination->submission_id,
                'ecard_id'                  => $model->user_nomination->ecard_id,
                'ecard_image'               => $ecard_image,
                //'campaign_type_id'          => $model->campaign->value_set_relation->campaign_id->id,
                //'nomination_id'             => $model->campaign,
                'user'                      => $model->user_nomination->user,
                'nominated_user'            => $nominated_user,
                'nominated_user_group_name' => $model->user_nomination->account->getRoleNames(),
                'account_id'                => $model->user_nomination->account_id,
                'nominated_by'              => $model->user_nomination->user_account, //$model->account,
                'nominated_by_group_name'   => $model->user_nomination->account->getRoleNames(),
                'user name'                 => optional($model->user_nomination->account)->name, //todo remove all optional and check all relation IN validation before insert
                'user email'                => optional($model->user_nomination->account)->email,
                'value'                     => ($model->user_nomination->points/10),
                'Type'                      => optional($model->user_nomination->type)->name ?? $model->user_nomination->reason,
                'value set'                 => optional($model->user_nomination->type)->value_set,
                'value_set_name'            => optional($model->user_nomination->type)->valueset,
                'level'                     => optional($model->user_nomination->level)->name,
                'points'                    => $model->user_nomination->points,
                'logo'                      => optional($model->user_nomination->type)->logo,
                'reason'                    => $model->user_nomination->reason,
                'attachments'               => ($model->user_nomination->attachments !='')?$imgUrl.$model->user_nomination->attachments:'',
                'Approved for level 1'      => $model->user_nomination->level_1_approval,
                'Approved for level 2'      => $model->user_nomination->level_2_approval,
                //'points'      => $model->points,
                'Decline reason'            => $model->user_nomination->decline_reason,
                'created_at'                => date('M d, Y h:i A', strtotime($model->user_nomination->created_at)),
                'updated_at'                => date('M d, Y h:i A', strtotime($model->user_nomination->updated_at)),
                'project_name'              => $model->user_nomination->project_name,
                'type'                      => $type,
                'notification_type_id'      => $model->notification_type,
                'mail_content'              => $model->mail_content,
                'created_date_time'         => $datetime,
            ];
        }else{
            //order

            $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
            $imgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/uploaded/user_nomination_files/';
            $productimgUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].'/storage/products_img/';

            $currentOrderStatus = 'Pending'; //status === 1 means pending
            if($model->user_order->status === 3){
                $currentOrderStatus = 'Shipped';
            } elseif($model->user_order->status === 2){
                $currentOrderStatus = 'Confirmed';
            } elseif($model->user_order->status === -1){
                $currentOrderStatus = 'Cancelled';
            }

            $account_ID = Helper::customCrypt($model->user_order->account_id);
            $user_data_program = ProgramUsers::where('account_id', $model->user_order->account_id)->first()->toArray();
            $user_info = $user_data_program;
            unset($user_info['id']);
            unset($user_info['account_id']);
            $user_info['id'] = Helper::customCrypt($user_data_program['id']);
            $user_info['account_id'] = Helper::customCrypt($user_data_program['account_id']);

            $pointss = $model->user_order->value / $model->user_order->quantity;

            #get receiver time as per receiver country
            $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $model->created_at);
            $country = DB::table('countries')->select('code')->where('id',$user_data_program['country_id'])->first();
            $timezone =  \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $country->code);
            if ($timezone) {
                $datetime->setTimezone($timezone[0]);
                $datetime = date('M d, Y h:i A', strtotime($datetime));
            }else{
                $datetime = date('M d, Y h:i A', strtotime($model->created_at));
            }

            return [
                'id'                 => Helper::customCrypt($model->id),
                'order_number'       => 'mrw-00'.$model->user_order->id,
                'value'              => $model->user_order->product->currency->code.' '.$model->user_order->denomination->value,
                'denomination_value' => $model->user_order->denomination->value,
                'order_value'        => $model->user_order->value,
                'quantity'           => $model->user_order->quantity,
                'points'             => (string)$pointss,
                'product'            => optional($model->user_order->product)->name,
                'image'              => $productimgUrl.optional($model->user_order->product)->image,
                'name'               => optional($model->user_order->account)->name,
                'account_id'         => $account_ID,
                'user'               => $user_info,
                'first_name'         => $model->user_order->first_name,
                'last_name'          => $model->user_order->last_name,
                'email'              => $model->user_order->email,
                'phone'              => $model->user_order->phone,
                'address'            => $model->user_order->address,
                'city'               => ucfirst($model->user_order->city),
                'comment'            => $model->user_order->comment,
                'country'            => ucfirst($model->user_order->country),
                'date'               => $model->user_order->created_at,
                'status'             => $model->user_order->status,
                'current_status'     => $currentOrderStatus,
                'is_gift'            => $model->user_order->is_gift,
                'type'               => 'order',
                'notification_type_id'=> $model->notification_type,
                'mail_content'       => $model->mail_content,
                'created_at'         => date('M d, Y h:i A', strtotime($model->created_at)),
                'created_date_time'         => $datetime,
            ];
        }

    }

}

