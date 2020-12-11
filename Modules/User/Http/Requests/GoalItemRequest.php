<?php namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoalItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
//            'user_id'    => 'required|exists:program_users',
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
