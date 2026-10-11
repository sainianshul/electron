<?php

namespace App\Http\Requests\Admin\Device;

use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $deviceId = $this->route('device') instanceof Device 
            ? $this->route('device')->id 
            : $this->route('device');

        return [
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'mac_address' => ['required', 'string', 'max:50', Rule::unique('devices', 'mac_address')->ignore($deviceId)],
            'description' => ['nullable', 'string', 'max:500'],
            'ip_address' => ['nullable', 'ip'],
            'status' => ['required', 'integer', Rule::in(array_keys(Device::getStatusList()))],
        ];
    }
}
