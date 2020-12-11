<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\NominationType;

class NominationTypeRepository extends Repository
{
    protected $modeler = NominationType::class;

    /**
     * @param $data
     * @param $pagination_count
     * @return mixed
     */
    public function filter($data , $pagination_count)
    {
        $query = (new $this->modeler)->query();


        if (isset($data['keyword'])) {
            $query->where('name', 'like' ,  '%' . $data['keyword'] . '%');
        }

        return $query->paginate($pagination_count);
    }

    /**
     * @param $value_set_id
     * @return mixed
     */
    public function getNominationTypeBy($value_set_id)
    {
        return NominationType::where('value_set', $value_set_id)->get();
    }
}
