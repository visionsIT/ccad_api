<?php namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class UserRipples extends Model
{
    //protected $fillable = ['sender_id'];
	protected $fillable = ['sender_id','receiver_id','group_id','points','point_type','campaign_id','is_active'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function types()
    {
        return $this->hasMany(NominationType::class,'value_set');
    }

  
}