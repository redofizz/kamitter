<?php

namespace App\Http\Requests;

use App\SystemManager;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class EditSystemManager extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $type_rule = Rule::in(array_keys(SystemManager::TYPE));

        return [
            'type' => 'required|' .$type_rule,
        ];
    }
}
