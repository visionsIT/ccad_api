<?php namespace Modules\Program\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Program\Models\ProgramsDomain;

class DomainsTransformer extends TransformerAbstract
{
    /**
     * @param ProgramsDomain $model
     *
     * @return array
     */
    public function transform(ProgramsDomain $model): array
    {
        return [
            'id'          => $model->id,
            'name'        => $model->name,
            'description' => $model->description,
            'is_primary'  => $model->is_primary,
        ];
    }
}
