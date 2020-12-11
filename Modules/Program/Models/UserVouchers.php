<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;

class UserVouchers extends Model
{
    protected $fillable = [ 'account_id', 'voucher_id', 'voucher_points', 'timezone'];
}
