<?php namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeOldPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
//            'old_password' => 'required|hash:' . auth()->password,
            'password'     => 'required|min:8|max:255|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/|confirmed',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
