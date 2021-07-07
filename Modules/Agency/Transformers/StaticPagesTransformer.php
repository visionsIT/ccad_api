<?php namespace Modules\Agency\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Agency\Models\Agency;
use Modules\Agency\Models\StaticPages;

class StaticPagesTransformer extends TransformerAbstract
{
    /**
     * @param Agency $agency
     *
     * @return array
     */
    public function transform(StaticPages $StaticPages): array
    {
        return [
            'id'   => $StaticPages->id,
        ];
    }
}