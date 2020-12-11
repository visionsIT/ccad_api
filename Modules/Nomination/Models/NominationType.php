<?php

namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\AccountBadges;

class NominationType extends Model
{
    protected $guarded =  [];
    public $timestamps = FALSE;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function awards_level()
    {
        return $this->hasMany(AwardsLevel::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function valueset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ValueSet::class, 'value_set');
    }
}


