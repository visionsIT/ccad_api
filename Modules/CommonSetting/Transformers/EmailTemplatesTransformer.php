<?php namespace Modules\CommonSetting\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\CommonSetting\Models\EmailTemplate;

class EmailTemplatesTransformer extends TransformerAbstract
{
    /**
     * @param AwardsLevel $model
     * @return array
     */
    public function transform(EmailTemplate $model): array
    {
        return [
            'id'                    => $model->id,
            'subject'               => $model->subject,
            'content'               => $model->content,
            'status'                => $model->status,
            'template_type'         => $model->templateType
        ];
    }

}
