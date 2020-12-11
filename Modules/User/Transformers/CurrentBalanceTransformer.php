<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Program;

class CurrentBalanceTransformer extends TransformerAbstract
{

    public function transform($tranformData): array
    {
        $data = json_decode($tranformData);
        return [
            'current_balance' => $data->current_bal,
            'user_nominations' => $data->nominations,
            'user_balance' => $data->balance
        ];
    }
}
