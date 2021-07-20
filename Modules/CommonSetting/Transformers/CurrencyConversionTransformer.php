<?php namespace Modules\CommonSetting\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\CommonSetting\Models\CurrencyConversion;
use Modules\Program\Models\Countries;


class CurrencyConversionTransformer extends TransformerAbstract
{
    /**
     * @param AwardsLevel $model
     * @return array
     */
    public function transform(CurrencyConversion $model): array
    {
        $from = Countries::select('currency_code')->where('id',$model->from_currency)->first();
        $to = Countries::select('currency_code')->where('id',$model->to_currency)->first();

        return [
            'id'                     => $model->id,
            'from_currency'          => $model->from_currency,
            'to_currency'            => $model->to_currency,
            'from_currency_name'     => $from->currency_code,
            'to_currency_name'       => $to->currency_code,
            'conversion'             => $model->conversion,
            'status'                 => $model->status,
        ];
    }

}
