<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ProgramUsers;

class UserCampaignsBudget extends Model
{

	protected $table = 'user_campaigns_budget';
    protected $fillable = ['program_user_id','campaign_id','budget','description'];

    public function user()
    {
        return $this->belongsTo(ProgramUsers::class, 'program_user_id');
    }
}