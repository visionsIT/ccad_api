<?php namespace Modules\Program\Observers;

use Modules\Access\Models\AccessType;
use Modules\Access\Models\RegistrationForm;
use Modules\Program\Models\Program;

class ProgramObserver
{
    /**
     * @param Program $program
     *
     * @return mixed
     */
    public function created(Program $program)
    {
        // todo: handle exception by validate the email in program
        AccessType::generateFor($program);
        return RegistrationForm::generateFor($program);
    }


}
