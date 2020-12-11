<?php namespace Modules\User\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\User\Models\Departments;

class DepartmentsTransformer extends TransformerAbstract
{
    /**
     * @param Departments $department
     *
     * @return array
     */
    public function transform(Departments $department): array
    {
        return [
            'id' => $department->id,
            'name' => $department->name
        ];
    }
}
