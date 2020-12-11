<?php namespace Modules\Agency\Http\Services;

use Modules\Agency\Http\Repositories\AgencyRepository;

class AgencyService
{
    protected $repository;

    /**
     * AgencyService constructor.
     *
     * @param AgencyRepository $repository
     */
    public function __construct(AgencyRepository $repository)
    {
        $this->repository = $repository;
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


    public function getClients($id)
    {
        return $this->repository->find($id)->clients;
    }
}
