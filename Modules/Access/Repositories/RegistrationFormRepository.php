<?php namespace Modules\Access\Repositories;

use App\Repositories\Repository;
use Modules\Access\Models\RegistrationForm;

class RegistrationFormRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = RegistrationForm::class;

}
