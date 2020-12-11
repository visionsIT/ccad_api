<?php namespace Modules\Program\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'return_to_budget'          => 'bool',
            'points_drain_notification' => 'required|min:0|max:10000000',
            'notifiable_agency_admins'  => 'array', // check if ids are exists
            'notifiable_client_admins'  => 'array',// check if ids are exists
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
