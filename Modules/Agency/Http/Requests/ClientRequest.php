<?php

namespace Modules\Agency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|string|unique:clients,name,' . $this->client, // regex:( /^[\w-]*$/ ) if you want include numbers
            'agency_id'       => 'required|exists:agencies,id',
            'catalogues_id.*' => 'required|exists:catalogues,id',
            'contact_name'    => 'required|string',
            'contact_email'   => 'required|email|regex:/^.+@.+$/i|unique:clients,contact_email,' . $this->client,
            'logo'            => 'nullable|image|max:2048 ' // |max:2048 for 2mb max image size
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
