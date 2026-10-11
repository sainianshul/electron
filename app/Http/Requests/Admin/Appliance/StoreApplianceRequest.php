<?php

namespace App\Http\Requests\Admin\Appliance;

use App\Models\Appliance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplianceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in([Appliance::TYPE_OUTPUT, Appliance::TYPE_INPUT])],
            'signal_type' => ['required', 'string', Rule::in([Appliance::SIGNAL_DIGITAL, Appliance::SIGNAL_PWM, Appliance::SIGNAL_ANALOG])],
            'supported_pin_types' => ['nullable', 'array'],
            'supported_pin_types.*' => ['string', 'in:DIGITAL,PWM,ANALOG'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'default_state' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'integer', Rule::in(array_keys(Appliance::getStatusList()))],
        ];
    }
}
