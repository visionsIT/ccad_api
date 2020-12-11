<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramsDomain extends Model
{

    protected $casts = [ 'is_primary' => 'bool' ];

    protected $fillable = [ 'name', 'program_id' ,  'description', 'is_primary' ];
}
