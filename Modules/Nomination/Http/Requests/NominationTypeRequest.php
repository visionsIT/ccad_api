<?php namespace Modules\Nomination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NominationTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'value_set' => 'required|exists:value_sets,id',
            'name' => 'required|unique:nomination_types',
            'points' => 'required|integer',
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
