<?php namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreatePasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'token'    => 'required',
            // 'password' => 'required|min:8|max:255|regex:/^(?=.*[A-Za-z])(?=.*\d).+$/|confirmed',
            'password' => 'required|min:8|max:255',
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'password.regex' => [ 'Password must contains numbers and letters' ]
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    /*public function authorize(): bool
    {
        return Auth::guest();
    }*/
}
