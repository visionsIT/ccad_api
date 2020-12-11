<?php namespace Modules\Access\Http\Services;

use Carbon\Carbon;
use Modules\Account\Models\Account;
use Modules\Access\Http\Repositories\AccessTypeRepository;
use Modules\Program\Models\Program;

/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class AccessTypeService
{
    protected $access_type_repository;

    /**
     * PasswordsService constructor.
     *
     * @param AccessTypeRepository $access_type_repository
     */
    public function __construct(AccessTypeRepository $access_type_repository)
    {
        $this->access_type_repository = $access_type_repository;
    }


    /**
     * @param $data
     * @param Carbon $carbon
     *
     * @return mixed
     */
    public function store($data, Carbon $carbon)
    {
        return $this->access_type_repository->create($data + [ 'last_login' => $carbon->now() ]);
    }

    /**
     * @param Program $program
     *
     * @return mixed
     */
    public function getProgramAccessType(Program $program)
    {
        return $program->accessType;
    }

    /**
     * @param $data
     * @param $id
     */
    public function update($data, $id): void
    {
        $this->access_type_repository->update($data, $id);
    }

}
