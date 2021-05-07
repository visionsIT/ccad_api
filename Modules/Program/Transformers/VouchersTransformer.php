<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Voucher;

class VouchersTransformer extends TransformerAbstract
{
    /**
     * @param Voucher $voucher
     *
     * @return array
     */
    
    public function transform(Voucher $model): array
    {
        $users = array();
        if($model->users != ''){
            $users = explode(',',$model->users);
        }
        return [
            'id'                    => $model->id,
            'voucher_name'          => $model->voucher_name,
            'voucher_points'        => $model->voucher_points,
            'start_datetime'        => $model->start_datetime,
            'end_datetime'        => $model->end_datetime,
            'timezone'        => $model->timezone,
            'quantity'        => $model->quantity,
            'used_count'        => $model->used_count,
            'description'        => $model->description,
            'status'        => $model->status,
            'created_at'        => $model->created_at,
            'updated_at'        => $model->updated_at,
        'users'                 => $users
        ];
    }
}
