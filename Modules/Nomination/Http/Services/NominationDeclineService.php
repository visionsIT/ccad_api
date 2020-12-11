<?php namespace Modules\Nomination\Http\Services;

use Modules\Nomination\Repositories\NominationDeclineRepository;

class NominationDeclineService
{
    private $repository;

    public function __construct(NominationDeclineRepository $repository)
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
     * @param $nomination
     * @param $data
     *
     * @return mixed
     */
    public function store($nomination , $data)
    {
        return $this->repository->create($data + ['nomination_id' => $nomination->id]);
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
