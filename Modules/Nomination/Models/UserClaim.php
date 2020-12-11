<?php

namespace Modules\Nomination\Models;

use App\Http\Resources\ProgramUser;
use Modules\Account\Models\Account;
use Illuminate\Database\Eloquent\Model;

class UserClaim extends Model
{
    protected   $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ClaimType::class, 'claim_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'approver_by');
    }

    public function scopeOfStatuses($query, $statuses = null)
    {
        return $query->where('approval_status', $statuses);
    }

}
