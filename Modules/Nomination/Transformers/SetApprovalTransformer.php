<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\SetApproval;

class SetApprovalTransformer extends TransformerAbstract
{
    /**
     * @param SetApproval $model
     *
     * @return array
     */
    public function transform(SetApproval $model): array
    {
        return [
            'id'                    => $model->id,
            'level_1_approval_type' => $model->level_1_approval_type,
            'level_1_permission'    => $model->level_1_permission,
            'level_1_user'          => unserialize($model->level_1_user),
            'level_1_group'         => $model->level_1_group,
            'level_2_approval_type' => $model->level_2_approval_type,
            'level_2_permission'    => $model->level_2_permission,
            'level_2_user'          => unserialize($model->level_2_user),
            'level_2_group'         => $model->level_2_group,
            'nomination_id'         => $model->nomination_id,
        ];
    }

}
