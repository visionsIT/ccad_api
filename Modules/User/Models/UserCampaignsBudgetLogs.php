<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserCampaignsBudgetLogs extends Model
{
    protected $table = 'user_campaigns_budget_logs';
    protected $fillable = ['program_user_id','campaign_id','budget','current_balance','description','created_by_id'];
}
