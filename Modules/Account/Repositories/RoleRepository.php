<?php namespace Modules\Account\Repositories;

use App\Repositories\Repository;
use Spatie\Permission\Models\Role;

class RoleRepository extends Repository
{
    protected $modeler = Role::class;

    /**
     * @param $id
     * @return mixed
     */
    public function getRolesByProgram($id, $search = '')
    {
        $response = $this->modeler->where('program_id', $id)->where('parent_id', 0);
            if($search !=''){
                $response = $response->where('name', 'like', '%' . $search . '%');
            }
        return $response = $response->orderBy('id', 'desc')->paginate(12);
    }

}
