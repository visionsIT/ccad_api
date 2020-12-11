<?php namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class AwardsLevel extends Model
{
    protected $fillable = ['name','description','points','nomination_type_id', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nomination_type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NominationType::class);
    }
}
