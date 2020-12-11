<?php namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignTypes extends Model
{
    protected $fillable = ['id','campaign_type','status'];

   
   /* public function campaign_type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ValueSet::class, 'value_set');
    }*/
}


