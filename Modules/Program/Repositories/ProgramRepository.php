<?php namespace Modules\Program\Repositories;

use App\Repositories\Repository;
use Modules\Program\Models\Program;

class ProgramRepository extends Repository
{
    protected $modeler = Program::class;

    public function filter($data , $pagination_count)
    {
        $query = (new $this->modeler)->query();

        if (isset($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (isset($data['keyword'])) {
            $query->where('name', 'like' ,  '%' . $data['keyword'] . '%');
        }

        return $query->get();
    }
}
