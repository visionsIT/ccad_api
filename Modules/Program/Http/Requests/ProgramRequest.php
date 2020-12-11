<?php namespace Modules\Program\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                => 'required|min:3|max:255',
            'reference'           => 'required|numeric',
            'agency_id'           => 'required|exists:agencies,id',
            'client_id'           => 'required|exists:clients,id',
            'currency_id'         => 'required|exists:currencies,id',
            'theme'               => 'required|numeric',
            'sent_from_email'     =>  'required|email|regex:/^.+@.+$/i|unique:programs,sent_from_email,' . $this->program,
            'contact_from_email'  => 'required|email|regex:/^.+@.+$/i|unique:programs,contact_from_email,' . $this->program,
            'google_analytics_id' => 'required',
            'google_tag_manager'  => 'required',
            'modules'             => 'required',
            'user_start_date'     => 'required|date',
            'user_end_date'       => 'required|date',
            'staging_password'    => 'required',
            'status'              => 'required',
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
