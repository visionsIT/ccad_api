<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;

class UsersEcards extends Model
{
    protected $fillable = [ 'ecard_id', 'campaign_id','sent_to', 'image_message', 'sent_by', 'points', 'send_type', 'send_datetime', 'send_timezone'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nominated_user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'sent_to','account_id');
    }
    public function nominated_by(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'sent_by','account_id');
    }
}