<?php namespace Modules\Agency\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Agency\Models\Agency;

class AgencyTransformer extends TransformerAbstract
{
    /**
     * @param Agency $agency
     *
     * @return array
     */
    public function transform(Agency $agency): array
    {
        return [
            'id'   => $agency->id,
            'name' => $agency->name,
        ];
    }
}