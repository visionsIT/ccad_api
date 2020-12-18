<?php

namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class ValueSet extends Model
{
    protected $fillable = ['name','description','program_id', 'status','campaign_type_id','anchor_status'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function types()
    {
        return $this->hasMany(NominationType::class,'value_set');
    }

    public function campaign_id(){
    	return $this->belongsTo(CampaignTypes::class, 'campaign_type_id','id');
    }

    public function wall_setings()
    {
        return $this->belongsTo(CampaignSettings::class, 'campaign_type_id','campaign_id');
    }

    public function Campaign_setting()
    {
        return $this->belongsTo(CampaignSettings::class, 'id','campaign_id');
    }

    public function usernomination()
    {
        return $this->hasMany(UserNomination::class, 'campaign_id');
    }


  
}
