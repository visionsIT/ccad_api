<?php

namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class CreateNominationTeam extends Model
{
    public $table = 'user_nomination_team';
    protected $fillable = ['id'];
}
