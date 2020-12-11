<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\Program;

class CurrentBalanceTransformer extends TransformerAbstract
{
    /**
     * @param Int $current_balance
     *
     * @return array
     */
    public function transform(Int $current_balance): array
    {
        return [
            'current_balance' => $current_balance,
        ];
    }
}
