<?php

namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    protected $fillable = ['name','status','value_set','multiple_recipient','approval_level','reporting'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function decline()
    {
        return $this->hasMany(NominationDecline::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function set_approval()
    {
        return $this->hasMany(SetApproval::class);
    }

    public function value_set_relation()
    {

        return $this->belongsTo(ValueSet::class, 'value_set');
    }

    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user_nomination()
    {
        return $this->hasMany(UserNomination::class, 'nomination_id');
    }

}
