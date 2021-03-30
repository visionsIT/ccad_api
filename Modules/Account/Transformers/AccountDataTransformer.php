<?php namespace Modules\Account\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Account\Models\Account;
use Spatie\Permission\Models\Role;
use Modules\User\Models\UsersPoint;
use Modules\Nomination\Models\CampaignSettings;
use Modules\User\Models\ProgramUsers;
use DB;


class AccountDataTransformer extends TransformerAbstract
{
    /**
     * @param Account $account
     *
     * @return array
     */
    public function transform($account): array
    {
       
        //echo "<pre>"; print_r($account); die;
        $data = '';
        if($account->query_type == 'order'){

            $data = DB::table('product_orders')->select('product_orders.id as order_id',DB::raw('DATE_FORMAT(product_orders.created_at, "%M %d, %Y %h:%i %p") as date'),'product_orders.comment as description','product_orders.quantity','product_orders.value','product_orders.account_id','product_orders.status',DB::raw("(SELECT balance FROM users_points
                          WHERE users_points.product_order_id = '".$account->id."' ORDER BY users_points.id DESC LIMIT 1
                        ) as balance"))->where(['product_orders.id' => $account->id])->get()->first();

            $status = 'Pending'; //status === 1 means pending
            if($data->status === 3){
                $status = 'Shipped';
            } elseif($data->status === 2){
                $status = 'Confirmed';
            } elseif($data->status === -1){
                $status = 'Cancelled';
            }

            $data->status = $status;

            $created_date = date('M d, Y g:i a', strtotime($data->date));
            $data->date = $created_date;


            $user_id = ProgramUsers::select('id')->where('account_id',$data->account_id)->first();
            
            $user_point = UsersPoint::select('balance')->where(['user_id'=>$user_id->id,'product_order_id'=>$data->order_id,'transaction_type_id'=>6])->latest()->first();
            

            $orderid = 'ccad-00'.$data->order_id;
            $data->order_id = $orderid;

            unset($data->account_id);

            if($data->balance == null){
                $user_point = UsersPoint::select('balance')->where('user_id',$user_id->id)->latest()->first();
                if(!empty($user_point)){
                    $data->balance = $user_point->balance;
                }
            }

        } elseif($account->query_type == 'sender' || $account->query_type == 'receiver'){
            $data = DB::table('user_nominations')->select(DB::raw('DATE_FORMAT(user_nominations.created_at, "%M %d, %Y %h:%i %p") as date'),'user_nominations.reason as description','value_sets.name as campaign','user_nominations.value','user_nominations.level_1_approval','user_nominations.level_2_approval','user_nominations.approver_account_id','user_nominations.l2_approver_account_id','user_nominations.rajecter_account_id','user_nominations.user as receiver','user_nominations.account_id as sender',DB::raw("(SELECT balance FROM users_points
                          WHERE users_points.product_order_id = '".$account->id."' ORDER BY users_points.id DESC LIMIT 1
                        ) as balance"))->leftjoin('value_sets','value_sets.id','user_nominations.campaign_id')->where(['user_nominations.id' => $account->id])->get()->first();

            $status = 'Pending';
            if( ($data->level_1_approval !== 2 || $data->level_2_approval !== 2) && $data->rajecter_account_id != ''
                ){
                $status = "Declined";
            }elseif( ($data->level_1_approval === 2 && $data->level_2_approval === 2) ||
                ($data->level_1_approval === 1 && $data->level_2_approval === 2 && $data->approver_account_id != '') ||
                ($data->level_1_approval === 2 && $data->level_2_approval === 1 && $data->l2_approver_account_id != '') ||
                ($data->level_1_approval === 1 && $data->level_2_approval === 1 && $data->approver_account_id != '' && $data->l2_approver_account_id != '')
                ){
                $status = 'Approved';
            }

            $data->status = $status;

            unset($data->level_1_approval);
            unset($data->level_2_approval);
            unset($data->approver_account_id);
            unset($data->l2_approver_account_id);
            unset($data->rajecter_account_id);

            $created_date = date('M d, Y g:i a', strtotime($data->date));
            $data->date = $created_date;

            #if_balnce_null_then_show_current_balance
            if($data->balance == null){
                if($account->query_type == 'sender'){
                    $user_id = ProgramUsers::select('id')->where('account_id',$data->sender)->first();
                    $user_point = UsersPoint::select('balance')->where('user_id',$user_id->id)->latest()->first();
                    if(!empty($user_point)){
                        $data->balance = $user_point->balance;
                    }
                }else{
                    $user_id = ProgramUsers::select('id')->where('account_id',$data->receiver)->first();
                    $user_point = UsersPoint::select('balance')->where('user_id',$user_id->id)->latest()->first();
                    if(!empty($user_point)){
                        $data->balance = $user_point->balance;
                    }
                }
                
            }

            unset($data->sender);
            unset($data->receiver);

        }
        return [
            'id'            =>  $account->id,
            'query_type'    =>  $account->query_type,
            'data'          =>  $data,
        ];
    }
}


