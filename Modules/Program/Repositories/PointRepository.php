<?php namespace Modules\Program\Repositories;

use App\Repositories\Repository;
use Modules\Program\Models\ProgramsPoint;
use Modules\User\Models\UsersPoint;

class PointRepository extends Repository
{
    protected $modeler = ProgramsPoint::class;

    /**
     * @param $program_id
     * @param $data
     *
     * @return mixed
     */
    public function updateOrCreate($program_id, $data)
    {
        return $this->modeler->updateOrCreate([ 'program_id' => $program_id ], $data);
    }

    /**
     * @param $program_id
     *
     * @return mixed
     */
    public function aggregateBalance($program_id)
    {
        return $this->modeler->where('program_id', $program_id)->sum('value');
    }

    public function filter($data, $pagination_count)
    {
        $query = (new $this->modeler)->query();

        if (isset($data['transaction_type_id'])) {
            $query->where('transaction_type_id', $data['transaction_type_id']);
        }

        if (isset($data['from_date'])) {
            $query->whereDate('from', '<=', $data['from_date']);
        }

        if (isset($data['to_date'])) {
            $query->whereDate('from', '>=', $data['to_date']);
        }

        return $query->get();

    }

    public function getPointsListing() {
        $listAllPoints = UsersPoint::orderBy('created_at', 'desc')->paginate(20);

        return response()->json($listAllPoints);
    }
}
