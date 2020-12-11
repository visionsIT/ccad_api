<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\ProgramsPointsExpiry;

class PointsExpiryTransformer extends TransformerAbstract
{
    /**
     * @param ProgramsPointsExpiry $model
     *
     * @return array
     */
    public function transform(ProgramsPointsExpiry $model): array
    {
        return [
            'id'                   => $model->id,
            'program_id'           => $model->program_id,
            'expiration_date'      => $model->expiration_date,
            'return_expiry_points' => $model->return_expiry_points,
        ];
    }
}
