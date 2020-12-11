<?php

namespace Modules\Access\Models;

use Illuminate\Database\Eloquent\Model;

class UserDataCustomField extends Model
{
    protected $table = 'user_data_custom_fields';

    protected $fillable = ['field', 'field_label', 'field_type', 'is_hidden', 'is_identifier'];
}
