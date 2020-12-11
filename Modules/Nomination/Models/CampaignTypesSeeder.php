<?php namespace Modules\Nomination\Models;

use Illuminate\Database\Eloquent\Model;

class NominationDecline extends Model
{
    protected $fillable = ['campaign_type','status'];
}
