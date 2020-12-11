<?php namespace Modules\Account\Http\Repositories;

use App\Repositories\Repository;
use Modules\Account\Models\Permission;

class PermissionRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = Permission::class;


    /**
     * @param $data
     *
     * @return mixed
     */
    public function search($data)
    {
        return $this->modeler->where('table_name', $data['table_name'])->get();
    }

}
