<?php
namespace Modules\Nomination\Http\Requests\UserNomination;

use Illuminate\Foundation\Http\FormRequest;

class GetRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules =  [
            'statuses' => 'nullable|in:0,1,-1',
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

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

}
