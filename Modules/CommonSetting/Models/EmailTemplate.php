<?php

namespace Modules\CommonSetting\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public $table = 'email_templates';

    protected $fillable = ['template_type_id','subject','content','status'];

    public function templateType()
    {
        return $this->belongsTo(EmailTemplateType::class, 'template_type_id', 'id');
    }
}
