<?php namespace Modules\Nomination\Http\Services;


use Modules\Nomination\Repositories\UserNominationRepository;

class UserNominationService
{
    private $repository;


    public function __construct(UserNominationRepository $repository)
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
