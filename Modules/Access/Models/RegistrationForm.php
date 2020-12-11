<?php

namespace Modules\Access\Models;

use Modules\Program\Models\Program;
use Illuminate\Database\Eloquent\Model;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Access\Enums\UserDataRegistration;
use Modules\Access\Presenters\RegistrationFormPresenter;

class RegistrationForm extends Model
{

    use Presentable;

    protected $presenter = RegistrationFormPresenter::class;

    protected $fillable = ['program_id', 'form'];

    /**
     * @param Program $program
     *
     * @return mixed
     */
    public static function generateFor(Program $program)
    {
        $form = UserDataRegistration::form();

        return self::create([
           'program_id' => $program->id,
           'form' => serialize($form)
        ]);

    }

}
