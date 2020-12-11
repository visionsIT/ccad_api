<?php namespace Modules\ValueSet\Http\Services;

use Modules\Nomination\Repositories\ValueSetRepository;

class ValueSetService
{
    private $repository;

    public function __construct(ValueSetRepository $repository)
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
        return $data ? $this->repository->filter($data, $pagination_count) : $this->repository->paginate($pagination_count);
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function store($data)
    {
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
