<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'           => 'required|min:3|max:255',
            'email'          => 'required|email|unique:accounts,email,' . $this->id,
            'contact_number' => 'required|numeric',
            'type'           => 'required|in:program_admin,global_admin',
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
//        return \Auth::id() === $this->id;
    }
}
