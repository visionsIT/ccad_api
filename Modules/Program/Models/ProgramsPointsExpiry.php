<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;
use Laracodes\Presenter\Traits\Presentable;
use Modules\Account\Models\Account;
use Modules\Program\Presenters\ProgramsPointPresenter;

class ProgramsPointsExpiry extends Model
{
    protected $fillable = [ 'program_id', 'expiration_date', 'return_expiry_points'];
}
