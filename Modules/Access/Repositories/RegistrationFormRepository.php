<?php namespace Modules\Access\Http\Repositories;

use App\Repositories\Repository;
use Modules\Access\Models\RegistrationForm;

class RegistrationFormRepository extends Repository
{
    /**
     * @var string
     */
    protected $modeler = RegistrationForm::class;

}
