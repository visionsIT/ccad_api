<?php namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\ProgramUsers;

class UsersSuggestion extends Model
{

    protected $fillable = [ 'user_id', 'suggestion', 'attachment' ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(ProgramUsers::class);
    }

}
