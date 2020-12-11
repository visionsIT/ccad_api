<?php

namespace Modules\Access\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccessTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'   => 'required|email|regex:/^.+@.+$/i|unique:access_types,email,' . $this->access_type,
            'account_locked_out_message' => 'required'
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
