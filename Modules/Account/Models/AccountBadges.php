<?php

namespace Modules\Account\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Nomination\Models\NominationType;

class AccountBadges extends Model
{
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }


    public function types()
    {
        return $this->hasMany(NominationType::class, 'id', 'nomination_type_id');
    }


}
