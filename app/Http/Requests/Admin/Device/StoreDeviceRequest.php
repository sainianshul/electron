<?php

namespace App\Http\Requests\Admin\Device;

use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'mac_address' => ['required', 'string', 'max:50', Rule::unique('devices', 'mac_address')],
            'description' => ['nullable', 'string', 'max:500'],
            'ip_address' => ['nullable', 'ip'],
            'status' => ['required', 'integer', Rule::in(array_keys(Device::getStatusList()))],
        ];
    }
}
