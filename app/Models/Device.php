<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'mac_address',
        'ip_address',
        'is_online',
        'last_seen_at',
        'status',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'status' => 'integer',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'ti ti-check',
            self::STATUS_INACTIVE => 'ti ti-x',
            default => 'ti ti-info-circle',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pins()
    {
        return $this->hasMany(DevicePin::class);
    }

    /**
     * Auto-creates standard 4 Digital + 1 PWM pins for NodeMCU ESP8266.
     */
    public function createDefaultPins(): void
    {
        $defaultPins = [
            ['pin_name' => 'D1', 'gpio_number' => 5, 'pin_type' => 'DIGITAL'],
            ['pin_name' => 'D2', 'gpio_number' => 4, 'pin_type' => 'DIGITAL'],
            ['pin_name' => 'D5', 'gpio_number' => 14, 'pin_type' => 'DIGITAL'],
            ['pin_name' => 'D6', 'gpio_number' => 12, 'pin_type' => 'DIGITAL'],
            ['pin_name' => 'D7', 'gpio_number' => 13, 'pin_type' => 'PWM'],
        ];

        foreach ($defaultPins as $pin) {
            $this->pins()->create([
                'pin_name' => $pin['pin_name'],
                'gpio_number' => $pin['gpio_number'],
                'pin_type' => $pin['pin_type'],
                'current_state' => '0',
                'is_assigned' => false,
            ]);
        }
    }
}
