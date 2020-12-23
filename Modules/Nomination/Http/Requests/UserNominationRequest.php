<?php namespace Modules\Nomination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserNominationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user'                => 'required',
            'nomination_id'       => 'required|numeric|exists:nominations,id',
            'value'               => 'required|numeric',
            'points'              => 'required|numeric',
            'reason'              => 'required|min:3|max:1000',
            'account_id'          => 'required|exists:accounts,id',

        ];
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
