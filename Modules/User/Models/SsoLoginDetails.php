<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class SsoLoginDetails extends Model
{
    protected $table = 'sso_login_details';
    protected $fillable = [ 'entity_id', 'sso_url', 'sl_url', 'x509' ];
}
