<?php namespace Modules\Program\Http\Repositories;

use App\Repositories\Repository;
use Modules\Program\Models\ProgramsPointsBudget;

class PointsBudgetRepository extends Repository
{
    protected $modeler = ProgramsPointsBudget::class;

    /**
     * @param $program_id
     * @param $data
     *
     * @return mixed
     */
    public function updateOrCreate($data, $program_id)
    {
        return $this->modeler->updateOrCreate([ 'program_id' => $program_id ], $data);
    }
}
