<?php namespace Modules\Program\Http\Services;

use Modules\Program\Http\Repositories\DomainRepository;

class DomainService
{
    private $repository;

    public function __construct(DomainRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @return mixed
     */
    public function get()
    {
        return $this->repository->get();
    }

    /**
     * @param $program
     * @param $data
     *
     * @return mixed
     */
    public function store($program , $data)
    {
        return $this->repository->create($data + ['program_id' => $program->id]);
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
