<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevicePin extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'pin_name',
        'gpio_number',
        'pin_type',
        'appliance_id',
        'custom_name',
        'current_state',
        'is_assigned',
    ];

    protected $casts = [
        'gpio_number' => 'integer',
        'is_assigned' => 'boolean',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function appliance()
    {
        return $this->belongsTo(Appliance::class);
    }
}
