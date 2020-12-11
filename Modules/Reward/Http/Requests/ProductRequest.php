<?php

namespace Modules\Reward\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'value'               => 'required|numeric',
            'image'               => 'required|min:3|max:255',
            'category_id'         => 'sometimes|exists:product_categories,id',
            'catalog_id'          => 'required|exists:product_catalogs,id',
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
