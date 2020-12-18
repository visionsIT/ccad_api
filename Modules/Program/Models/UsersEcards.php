<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Account\Models\Account;


class UsersEcards extends Model
{
    protected $fillable = [ 'ecard_id', 'campaign_id','sent_to', 'image_message', 'sent_by', 'points', 'send_type', 'send_datetime', 'send_timezone','new_image','image_path'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nominated_user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'sent_to','id');
    }
    public function nominatedto(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'sent_to');
    }
    public function nominated_by(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'sent_by','id');
    }
    public function nominatedby(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\User\Models\ProgramUsers::class, 'sent_by');
    }
    public function user_nomination(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Nomination\Models\UserNomination::class, 'id','ecard_id');
    }
}