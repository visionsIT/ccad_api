<?php

namespace Modules\CommonSetting\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateType extends Model
{   
    public $table = 'email_template_types';

    protected $fillable = ['template_name','dynamic_code','description','status'];
}
