<?php namespace Modules\Program\Models;

use Illuminate\Database\Eloquent\Model;

class Ecards extends Model
{
    protected $fillable = [ 'card_title', 'card_image', 'status', 'allow_points','campaign_id'];
}