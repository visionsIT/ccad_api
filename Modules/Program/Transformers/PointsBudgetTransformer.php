<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\ProgramsPointsBudget;

class PointsBudgetTransformer extends TransformerAbstract
{
    /**
     * @param ProgramsPointsBudget $model
     *
     * @return array
     */
    public function transform(ProgramsPointsBudget $model): array
    {
        return [
            'id'                        => $model->id,
            'program_id'                => $model->program_id,
            'is_disabled'               => $model->is_disabled,
            'return_to_budget'          => $model->return_to_budget,
            'points_drain_notification' => $model->points_drain_notification,
            'notifiable_agency_admins'  => $model->notifiable_agency_admins,
            'notifiable_client_admins'  => $model->notifiable_client_admins,
        ];
    }
}
