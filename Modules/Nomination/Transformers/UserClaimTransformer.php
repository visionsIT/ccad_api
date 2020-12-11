<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\UserClaim;

class UserClaimTransformer extends TransformerAbstract
{
    /**
     * @param UserClaim
     *
     * @return array
     */
    public function transform(UserClaim $model): array
    {
        return [
            'claim_id'                => $model->id,
            'user_first_name'         => $model->user->first_name,
            'user_last_name'         => $model->user->last_name,
            'user_email'        => $model->user->email,
            'claim_type'        => $model->claimType->name,
            'claim_points'      => $model->claimType->points,
            'claim_reason'      => $model->reason,
            'attachment_file'   => $model->attachment_path,
        ];
    }

}
