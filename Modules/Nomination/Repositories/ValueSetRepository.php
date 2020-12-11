<?php namespace Modules\Nomination\Repositories;

use App\Repositories\Repository;
use Modules\Nomination\Models\ValueSet;

class ValueSetRepository extends Repository
{
    protected $modeler = ValueSet::class;

    public function filter($data, $pagination_count)
    {
        $query = (new $this->modeler)->query();


        if (isset($data['keyword'])) {
            $query->where('name', 'like', '%' . $data['keyword'] . '%')->orwhere('description', 'like', '%' . $data['keyword'] . '%');
        }

        return $query->paginate($pagination_count);
    }

}
