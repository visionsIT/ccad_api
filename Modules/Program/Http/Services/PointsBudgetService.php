<?php namespace Modules\Program\Http\Services;

use Modules\Program\Repositories\PointsBudgetRepository;

class PointsBudgetService
{
    private $repository;

    public function __construct(PointsBudgetRepository $repository)
    {
        $this->repository = $repository;
    }

    public function selectProgramBudgetStatus($program_id)
    {
        return $this->repository->select([ 'budget_status' ])->find($program_id);
    }

    /**
     * @param $data
     * @param $program_id
     */
    public function update($data, $program_id): void
    {
        $this->repository->updateOrCreate($data, $program_id);
    }

}
