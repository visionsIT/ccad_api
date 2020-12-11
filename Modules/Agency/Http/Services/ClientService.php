<?php namespace Modules\Agency\Http\Services;

use Modules\Agency\Http\Repositories\ClientRepository;

/**
 * Class ClientService
 *
 * @package Modules\Agency\Http\Services
 */
class ClientService
{
    protected $repository;

    /**
     * AgencyService constructor.
     *
     * @param ClientRepository $repository
     */
    public function __construct(ClientRepository $repository)
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
}
