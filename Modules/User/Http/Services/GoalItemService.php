<?php namespace Modules\User\Http\Services;

use Illuminate\Validation\ValidationException;
use Modules\User\Http\Repositories\GoalItemRepository;
use Modules\User\Http\Repositories\PointRepository;

class GoalItemService
{
    private $repository;

    public function __construct(GoalItemRepository $repository)
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
     * @param $user_id
     * @param $data
     *
     * @return mixed
     */
    public function store($user_id, $data)
    {
        $data['user_id'] = $user_id;

        return $this->repository->updateOrCreate($user_id, $data);
    }


    /**
     * @param $user_id
     * @param $data
     *
     * @return mixed
     */
    public function remove($user_id, $data)
    {
        $data['user_id'] = $user_id;

        return $this->repository->removeGoalItem($user_id,$data);
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function getUserGoalItem($id)
    {
        return $this->repository->getUserGoalItem($id);
    }


}
