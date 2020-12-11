<?php namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Account\Models\Account;

class AgenciesAdmin extends Model
{
    use SoftDeletes;

    protected $fillable = [ 'agency_id' , 'account_id' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class)->where('type', 'agency_admin');
    }

}
