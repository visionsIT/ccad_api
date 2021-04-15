<?php namespace app\Http\Services;

use Carbon\Carbon;
use Modules\Account\Models\Account;
use Modules\User\Models\UsersGroupList;
use DateTime;
use DB;
/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class AuthLoginService
{
     /**
     * PasswordsService constructor.
     *
     * @param AccountRepository $account_repository
     */
   
    /*******Overall REport*****/
    public function increaseLoggedUserCount($accountid,$ip_address) {
        
        $ip_address = $ip_address;
        
        if(isset($accountid)){
            $count = DB::table('page_visits')->where('account_id',$accountid)->where('created_at', 'like', date('Y-m-d%'))->count();
            $old_count = DB::table('page_visits')->where('account_id',$accountid)->count();
            $account_id = $accountid;
        }
        else{
            $count = DB::table('page_visits')->where('user_ip',$ip_address)->where('created_at', 'like', date('Y-m-d%'))->count();
            $old_count = DB::table('page_visits')->where('user_ip',$ip_address)->count();
            $account_id = NULL;
        }
        if($count == 0){
            
            
            if($old_count > 0){
                $is_unique = '0';
            }
            else{
                $is_unique = '1';
            }
            $insert_data = [
                'page_name' => 'login',
                'visits_count' => '1',
                'user_ip' => $ip_address,
                'account_id' => $account_id,
                'is_unique' => $is_unique,
                'created_at' =>date('Y-m-d h:i:s'),
                'updated_at' =>date('Y-m-d h:i:s'),
            ];
            $insert =   DB::table("page_visits")->insert($insert_data);
            return response()->json(['message'=>'Successfully  executed.', 'status'=>'success']);
            exit;
        }
        else{
            if(isset($accountid)){
                $old_data = DB::table("page_visits")->where('account_id',$account_id)->where('created_at', 'like', date('Y-m-d%'))->first();
                $update_data = [
                    'visits_count' => $old_data->visits_count+1,
                    'updated_at' =>date('Y-m-d h:i:s'),
                    
                ];
                $update = DB::table("page_visits")->where('account_id',$account_id)->where('created_at', 'like', date('Y-m-d%'))->update($update_data);
            }
            else{
                $old_data = DB::table("page_visits")->where('user_ip',$ip_address)->where('created_at', 'like', date('Y-m-d%'))->first();
                $update_data = [
                    'visits_count' => $old_data->visits_count+1,
                    'updated_at' =>date('Y-m-d h:i:s'),
                    
                ];
                $update = DB::table("page_visits")->where('user_ip',$ip_address)->where('created_at', 'like', date('Y-m-d%'))->update($update_data);
            }
            
            return response()->json(['message'=>'Successfully  executed.', 'status'=>'success']);
            exit;
        }
        
    }

}
