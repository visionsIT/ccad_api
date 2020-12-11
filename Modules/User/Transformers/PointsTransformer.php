<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\UsersPoint;

class PointsTransformer extends TransformerAbstract
{
    /**
     * @param ProgramsPoint $model
     *
     * @return array
     * @throws \Laracodes\Presenter\Exceptions\PresenterException
     */
    public function transform(UsersPoint $model): array
    {
        return [
            'id'               => $model->id,
            'value'            => $model->value,
            'user_id'          => $model->user_id,
            'transaction_type' => $model->present()->transaction_type,
            'transaction_type_id' => $model->transaction_type_id,
            'description'      => $model->description,
            'balance'          => $model->balance,
            'attachment'          => $model->attachment,
            'created_at'       => $model->created_at,
            'created_by'       => [
                'name' => $model->present()->created_by
            ],
            'created_date_time'         => date('M d, Y h:i A', strtotime($model->created_at)), //April 15 2014 10:30pm
        ];
    }
}
