<?php namespace Modules\Account\Repositories;

use Carbon\Carbon;
use DB;

/**
 * Class CitiesRepository
 *
 * @package Modules\Account\Repositories
 */
class TokensRepository
{
    protected $expire;

    /**
     * @param $data
     *
     * @return bool
     */
    public function create($data)
    {
        $check_existing_token = DB::table('tokens')->where($data)->first();
        if(!empty($check_existing_token)){
            return DB::table('tokens')->where($data)->update([ 'created_at' => Carbon::now() ]);
        }else{
            return DB::table('tokens')->insert($data + [ 'created_at' => Carbon::now() ]);
        }
        
    }

    /**
     * @param $token
     * @param $type || type 0 for password resets &&  type 1 for activation link
     *
     * @return mixed
     */
    public function findToken($token, $type)
    {
        return DB::table('tokens')->where('token', $token)->where('type', $type)->first();
    }

    /**
     * Delete expired tokens.
     *
     * @return int
     */
    public function deleteExpiredTokens()  // TODO edit for code
    {
        $expiredAt = Carbon::now()->subDays(1);
        return DB::table('tokens')->where('created_at', '<', $expiredAt)->delete();
    }

    /**
     * @param $account_id
     * @param $type
     *
     * @return int
     */
    public function deleteColumnViaAccountId($account_id, $type)
    {
        return DB::table('tokens')->where('account_id', $account_id)->where('type', $type)->delete();
    }

    /**
     * @param $column
     * @param $value
     *
     * @return int
     */
    public function getCount($column, $value)
    {
        return DB::table('tokens')->where($column, $value)->count();
    }
}