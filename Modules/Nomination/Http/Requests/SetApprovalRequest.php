<?php namespace Modules\Nomination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetApprovalRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'level_1_user.*' => 'sometimes|exists:program_users,id',
            'level_2_user.*' => 'sometimes|exists:program_users,id',
            'level_1_approval_type' => 'required|min:3|max:255',

        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

}
