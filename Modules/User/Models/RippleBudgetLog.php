<?php namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ProgramUsers;

class RippleBudgetLog extends Model
{
    protected $table = 'ripple_budget_log';
    protected $fillable = [ 'id', 'ripple_budget', 'type', 'user_id', 'current_balance', 'created_by_id' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(ProgramUsers::class, 'created_by_id');
    }


}
