<?php namespace Modules\Nomination\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Nomination\Models\ValueSet;
use DB;
use Helper;
class ValueSetTransformer extends TransformerAbstract
{
    /**
     * @param ValueSet $ValueSet
     *
     * @return array
     */
    public function transform(ValueSet $model): array
    {

    
        $campain_type =  DB::table('value_sets')->select('campaign_types.campaign_type')->where(['value_sets.id' => $model->id])->join('campaign_types', 'campaign_types.id', '=', 'value_sets.campaign_type_id')->get()->first();

        return [
            'id'                    => Helper::customCrypt($model->id) ,
            'name'                  => $model->name,
            'description'           => $model->description,
            'values'                => $model->types->count(),
            'status'                => $model->status,
            'campaign_type_id'      => $model->campaign_type_id,
            'campaign_name'         => $campain_type ? $campain_type->campaign_type : '',
            'anchor_status'         => $model->anchor_status
        ];
    }

}
