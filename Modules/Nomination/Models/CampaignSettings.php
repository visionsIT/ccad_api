<?php namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignSettings extends Model
{

	public $table = 'campaign_settings';
    protected $fillable = ['id','campaign_id','send_multiple_status','approval_request_status','level_1_approval','level_2_approval','budget_type', 'min_point', 'max_point','points_allowed'];

   
   /* public function campaign_type(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ValueSet::class, 'value_set');
    }*/
}


