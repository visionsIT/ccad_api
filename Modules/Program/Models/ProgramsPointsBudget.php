<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramsPointsBudget extends Model
{
    protected $casts = [ 'notifiable_agency_admins' => 'array', 'notifiable_client_admins' => 'array' ];

    protected $fillable = ['program_id' , 'is_disabled', 'return_to_budget', 'points_drain_notification', 'notifiable_agency_admins',
                           'notifiable_client_admins' ];
}
