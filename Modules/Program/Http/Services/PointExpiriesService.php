<?php namespace Modules\Program\Http\Services;

use Modules\Program\Http\Repositories\PointRepository;

class PointExpiriesService
{
    private $repository;

    public function __construct(PointRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handleExpiration($program, $data)
    {
        return isset($data['points_expiry_enabled'] ) && $data['points_expiry_enabled'] ? $this->store($program, $data) : $this->destroy($program);
    }

    /**
     * @param $program
     * @param $data
     *
     * @return mixed
     */
    public function store($program, $data)
    {
        return $this->repository->updateOrCreate($program->id, $data);
    }

    /**
     * @param $program
     *
     * @return mixed|void
     */
    public function destroy($program)
    {
        $this->repository->destroy($program->points_expiry->id);
    }

}
