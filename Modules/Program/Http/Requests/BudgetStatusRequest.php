<?php namespace Modules\Program\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BudgetStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'is_disabled' => 'required|bool',
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
