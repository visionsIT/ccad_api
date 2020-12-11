<?php

namespace Modules\Account\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'    => 'required|string|unique:roles',
            'program_id' => 'required|exists:programs,id',
            'parent_id' => 'sometimes|exists:roles,id',
            'group_level_id' => 'required|exists:group_levels,id',
            'group_level_parent_id' => 'sometimes'
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
