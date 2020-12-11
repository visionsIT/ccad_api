<?php namespace Modules\Program\Http\Services;

use Modules\Program\Http\Repositories\ProgramRepository;

class ProgramService
{
    private $repository;

    public function __construct(ProgramRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @param $pagination_count
     * @param array $data
     *
     * @return mixed
     */
    public function get($pagination_count, $data = [])
    {
//        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->paginate($pagination_count);
        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->get();
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function store($data)
    {
        $data['modules'] = json_encode($data['modules']); // needs to be updated

        return $this->repository->create($data);
    }


    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param $data
     * @param $id
     */
    public function update($data, $id): void
    {
        $data['modules'] = json_encode($data['modules']); // needs to be updated

        $this->repository->update($data, $id);
    }


    /**
     * @param $id
     */
    public function destroy($id): void
    {
        $this->repository->destroy($id);
    }

}
