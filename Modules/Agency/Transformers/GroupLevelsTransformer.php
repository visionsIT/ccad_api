<?php namespace Modules\Agency\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Agency\Models\GroupLevels;

class GroupLevelsTransformer extends TransformerAbstract
{
    /**
     * @param Levels $levels
     *
     * @return array
     */
    public function transform(GroupLevels $levels): array
    {
        return [
            'id'   => $levels->id,
            'name' => $levels->name,
            'description' => $levels->description,
            'status' => $levels->status,
        ];
    }
}
