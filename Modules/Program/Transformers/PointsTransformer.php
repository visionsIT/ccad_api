<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\ProgramsPoint;

class PointsTransformer extends TransformerAbstract
{
    /**
     * @param ProgramsPoint $model
     *
     * @return array
     * @throws \Laracodes\Presenter\Exceptions\PresenterException
     */
    public function transform(ProgramsPoint $model): array
    {
        return [
            'id'               => $model->id,
            'value'            => $model->value,
            'program_id'       => $model->program_id,
            'transaction_type' => $model->present()->transaction_type,
            'description'      => $model->description,
            'balance'          => $model->balance,
            'created_at'       => $model->created_at,
            'created_by'       => [
                'name' => $model->present()->created_by
            ]
        ];
    }
}
