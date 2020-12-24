<?php 
namespace Modules\Nomination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeamNominationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            // 'user'                  =>  'required|numeric|exists:accounts,id',
            'nomination_id'         =>  'required|numeric|exists:nominations,id',
            // 'value'                 =>  'required|numeric',
            // 'points'                =>  'required|numeric',
            'reason'                =>  'required|min:3',
            // 'account_id'            =>  'required|numeric|exists:accounts,id',
            'project_name'          =>  'required|min:3|max:191',
            'users'                 =>  'required'
        ];
        // foreach($this->request->get('users') as $key => $val)
        //     $rules['users.'.$key.'.accountid']  = 'required|numeric|exists:accounts,id';
        //     $rules['users.'.$key.'.value']      = 'required|numeric';
        // }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return TRUE;
    }
}
