<?php namespace Modules\Nomination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NominationReportExportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules =  [
            'status' => 'nullable|in:0,1,-1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date'
        ];
        if($this->has('end_date')) {
            $rules['start_date'] = 'nullable|date|before_or_equal:end_date';
        }
        if($this->has('start_date')) {
            $rules['end_date'] = 'nullable|date|after_or_equal:start_date';
        }
        return  $rules;
    }

}
