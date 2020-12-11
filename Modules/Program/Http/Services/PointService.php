<?php namespace Modules\Program\Http\Services;

use Illuminate\Validation\ValidationException;
use Modules\Program\Http\Repositories\PointRepository;

class PointService
{
    private $repository;

    public function __construct(PointRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @param $pagination_count
     * @param $proram_id
     * @param array $data
     *
     * @return mixed
     */
    public function get($pagination_count, $proram_id, $data = [])
    {
//        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->paginate($pagination_count);
        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->get();
    }

    /**
     * @param $program
     * @param $data
     *
     * @return mixed
     */
    public function store($program, $data)
    {
        $current_balance = $this->currentBalance($program);

        // throw an exception if the deduction value is greater than the current balance of points
        if ($data['value'] < 0 && abs($data['value']) > $current_balance) {
            throw ValidationException::withMessages([ 'value' => 'This Programme does not have enough points to remove this amount' ]);
        }

        $data['created_by_id']       = \Auth::id();
        $data['balance']             = $current_balance + $data['value'];
        $data['transaction_type_id'] = 9; // Program points

        return $this->repository->create($data + [ 'program_id' => $program->id ]);
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function show($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param $program
     *
     * @return mixed
     */
    public function currentBalance($program)
    {
        return $this->repository->aggregateBalance($program->id);
    }

    public function pointsHistory()
    {
        return $this->repository->getPointsListing();
    }

}
