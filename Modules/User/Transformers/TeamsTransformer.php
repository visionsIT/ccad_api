<?php 
namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\Teams;

class TeamsTransformer extends TransformerAbstract
{
    /**
     * @param Teams $team
     *
     * @return array
     */
    public function transform(Teams $team): array
    {
        return [
            'id'        =>  $team->id,
            'name'      =>  $team->name,
            'department' =>  (!empty($team->department)) ? $team->department->name : ''
        ];
    }
}
