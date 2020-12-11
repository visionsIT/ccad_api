<?php namespace Modules\Agency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgenciesAdminsRequest extends FormRequest
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
            'email'          => 'required|email|unique:accounts,email,' . $this->admin,
            'contact_number' => 'required',
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
