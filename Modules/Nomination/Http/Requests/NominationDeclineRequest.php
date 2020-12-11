<?php namespace Modules\Nomination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NominationDeclineRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'description'                => 'required|min:3|max:255',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

}
