<?php namespace Modules\Access\Transformers;

use Modules\Access\Models\AccessType;
use League\Fractal\TransformerAbstract;

class AccessTypeTransformer extends TransformerAbstract
{
    /**
     * @param AccessType $access_type
     *
     * @return array
     */
    public function transform(AccessType $access_type): array
    {
        return [
            'id'   => $access_type->id,
            'email' => $access_type->email,
            'program_id'   => $access_type->program_id,
            'reset_password_option' => $access_type->reset_password_option,
            'way_to_access_the_program' => $access_type->way_to_access_the_program,
            'register_require_approval' => $access_type->register_require_approval,
            'account_locked_out_message' => $access_type->account_locked_out_message,
        ];
    }
}

