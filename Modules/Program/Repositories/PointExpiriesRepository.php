<?php namespace Modules\Program\Http\Repositories;

use App\Repositories\Repository;
use Modules\Program\Models\ProgramsPointsExpiry;

class PointExpiriesRepository extends Repository
{
    protected $modeler = ProgramsPointsExpiry::class;

    /**
     * @param $data
     * @param $pagination_count
     *
     * @return mixed
     */
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

        return $query->paginate($pagination_count);
    }
}
