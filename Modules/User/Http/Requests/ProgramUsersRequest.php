<?php namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramUsersRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:program_users,email|unique:accounts,email',
            'username' => 'required|unique:program_users,username|unique:accounts,email',
            'group_id' => 'required|exists:roles,id',
            'role_id' => 'required|exists:user_roles,id',
            'password' => 'required',
            'language' => 'required',
            'company' => 'required',
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
