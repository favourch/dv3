<?php

namespace Modules\Clickpaysa\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClickpaySettings extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'settings.clickpay_profile_id' => 'required',
            'settings.clickpay_server_key' => 'required',
            'settings.clickpay_currency' => 'required'
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'settings.clickpay_profile_id.required' => __('This field is required.'),
            'settings.clickpay_server_key.required' => __('This field is required.'),
            'settings.clickpay_currency.required' => __('This field is required.'),
        ];
    }
}
