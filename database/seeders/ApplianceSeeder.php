<?php

namespace Database\Seeders;

use App\Models\Appliance;
use Illuminate\Database\Seeder;

class ApplianceSeeder extends Seeder
{
    public function run(): void
    {
        $appliances = [
            [
                'name'                => 'Ceiling Fan',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-propeller',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'LED Bulb',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-bulb',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Tube Light',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-lamp',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Smart Switch',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-toggle-right',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Water Motor',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-droplet',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'AC (Air Conditioner)',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-air-conditioning',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Geyser',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-flame',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Exhaust Fan',
                'type'                => 'OUTPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-wind',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Dimmer Light',
                'type'                => 'OUTPUT',
                'signal_type'         => 'PWM',
                'supported_pin_types' => ['PWM'],
                'icon'                => 'ti ti-brightness-half',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Fan Regulator',
                'type'                => 'OUTPUT',
                'signal_type'         => 'PWM',
                'supported_pin_types' => ['PWM'],
                'icon'                => 'ti ti-adjustments',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Temperature Sensor',
                'type'                => 'INPUT',
                'signal_type'         => 'ANALOG',
                'supported_pin_types' => ['ANALOG'],
                'icon'                => 'ti ti-temperature',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
            [
                'name'                => 'Motion Sensor',
                'type'                => 'INPUT',
                'signal_type'         => 'DIGITAL',
                'supported_pin_types' => ['DIGITAL'],
                'icon'                => 'ti ti-radar',
                'default_state'       => '0',
                'status'              => 1,
                'created_by'          => 1,
            ],
        ];

        foreach ($appliances as $data) {
            Appliance::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
