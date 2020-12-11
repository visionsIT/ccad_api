<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [ 'voucher_name', 'voucher_points', 'start_datetime', 'end_datetime', 'quantity', 'timezone', 'used_count', 'description', 'status'];

    public static function getVouchers($filterData){

        if($filterData['searchText'] !=''){
            $response = Voucher::where('voucher_name', 'like', '%' . $filterData['searchText'] . '%')->orderBy($filterData['col'], $filterData['orderBy'])->paginate(10);
        } else {
            $response = Voucher::orderBy($filterData['col'], $filterData['orderBy'])->paginate(10);
        }
        return $response;
    }
}
