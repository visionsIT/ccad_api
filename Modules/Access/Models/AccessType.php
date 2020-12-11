<?php

namespace Modules\Access\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Program\Models\Program;

class AccessType extends Model
{
    protected $fillable = ['email', 'account_locked_out_message', 'program_id', 'way_to_access_the_program', 'register_require_approval', 'reset_password_option'];

    /**
     * @param Program $program
     *
     * @return mixed
     */
    public static function generateFor(Program $program)
    {
        return self::create([
            'email' => $program->contact_from_email,
            'program_id' => $program->id,
            'way_to_access_the_program' => 'self_registration',
            'account_locked_out_message' => ''
        ]);
    }

}
