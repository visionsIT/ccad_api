<?php namespace Modules\Access\Http\Services;

use Carbon\Carbon;
use Modules\Program\Models\Program;
use Modules\Access\Http\Repositories\RegistrationFormRepository;

/**
 * Class PasswordsService
 *
 * @package Modules\Account\Http\Service
 */
class RegistrationFormService
{
    protected $registration_form_repository;

    /**
     * PasswordsService constructor.
     *
     * @param RegistrationFormRepository $registration_form_repository
     */
    public function __construct(RegistrationFormRepository $registration_form_repository)
    {
        $this->registration_form_repository = $registration_form_repository;
    }


    /**
     * @param $data
     * @param Carbon $carbon
     *
     * @return mixed
     */
    public function store($data, Carbon $carbon)
    {
        return $this->registration_form_repository->create($data + [ 'last_login' => $carbon->now() ]);
    }

    /**
     * @param Program $program
     *
     * @return mixed
     */
    public function getProgramRegistrationForm(Program $program)
    {
        return $program->registrationForm;
    }

    /**
     * @param $data
     * @param $id
     */
    public function update($data, $id): void
    {
        $data['form'] = serialize($data['form']);

        $this->registration_form_repository->update($data, $id);
    }

}
