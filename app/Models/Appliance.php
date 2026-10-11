<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appliance extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public const TYPE_OUTPUT = 'OUTPUT';
    public const TYPE_INPUT = 'INPUT';

    public const SIGNAL_DIGITAL = 'DIGITAL';
    public const SIGNAL_PWM = 'PWM';
    public const SIGNAL_ANALOG = 'ANALOG';

    protected $fillable = [
        'name',
        'type',
        'signal_type',
        'supported_pin_types',
        'icon',
        'image',
        'default_state',
        'status',
        'created_by',
    ];

    protected $casts = [
        'supported_pin_types' => 'array',
        'status' => 'integer',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public static function getTypeList(): array
    {
        return [
            self::TYPE_OUTPUT => 'Output',
            self::TYPE_INPUT => 'Input',
        ];
    }

    public static function getSignalTypeList(): array
    {
        return [
            self::SIGNAL_DIGITAL => 'Digital',
            self::SIGNAL_PWM => 'PWM',
            self::SIGNAL_ANALOG => 'Analog',
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

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return filter_var($this->image, FILTER_VALIDATE_URL) 
            ? $this->image 
            : asset('storage/' . $this->image);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pins()
    {
        return $this->hasMany(DevicePin::class);
    }
}
