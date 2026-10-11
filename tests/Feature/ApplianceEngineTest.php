<?php

namespace Tests\Feature;

use App\Models\Appliance;
use App\Models\Device;
use App\Models\User;
use App\Services\ApplianceEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplianceEngineTest extends TestCase
{
    use RefreshDatabase;

    protected ApplianceEngine $engine;
    protected User $user;
    protected Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(ApplianceEngine::class);

        $this->user = User::factory()->create(['role' => User::ROLE_USER]);
        
        $this->device = Device::create([
            'user_id' => $this->user->id,
            'name' => 'Living Room Board',
            'mac_address' => 'AA:BB:CC:DD:EE:01',
            'status' => Device::STATUS_ACTIVE,
        ]);
        $this->device->createDefaultPins(); // 4 Digital + 1 PWM
    }

    public function test_slot_availability_detection(): void
    {
        $fan = Appliance::create([
            'name' => 'Ceiling Fan',
            'type' => 'OUTPUT',
            'signal_type' => 'DIGITAL',
            'supported_pin_types' => ['DIGITAL'],
            'default_state' => '0',
            'status' => Appliance::STATUS_ACTIVE,
        ]);

        $check = $this->engine->checkSlotAvailability($this->device, $fan);

        $this->assertTrue($check['available']);
        $this->assertEquals(4, $check['free_count']);
    }

    public function test_smart_allocation_and_slot_exhaustion(): void
    {
        $bulb = Appliance::create([
            'name' => 'LED Bulb',
            'type' => 'OUTPUT',
            'signal_type' => 'DIGITAL',
            'supported_pin_types' => ['DIGITAL'],
            'default_state' => '0',
            'status' => Appliance::STATUS_ACTIVE,
        ]);

        // Allocate 4 digital slots
        for ($i = 1; $i <= 4; $i++) {
            $pin = $this->engine->allocateAppliance($this->device, $bulb, "Bulb {$i}");
            $this->assertTrue($pin->is_assigned);
        }

        // 5th allocation of DIGITAL must fail because all 4 digital slots are full
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/No empty DIGITAL slot available/i');
        $this->engine->allocateAppliance($this->device, $bulb, "Extra Bulb");
    }

    public function test_pwm_appliance_uses_pwm_channel(): void
    {
        $dimmer = Appliance::create([
            'name' => 'Dimmer Light',
            'type' => 'OUTPUT',
            'signal_type' => 'PWM',
            'supported_pin_types' => ['PWM'],
            'default_state' => '0',
            'status' => Appliance::STATUS_ACTIVE,
        ]);

        $pin = $this->engine->allocateAppliance($this->device, $dimmer, "Chandelier Dimmer");
        $this->assertEquals('D7', $pin->pin_name);
        $this->assertEquals('PWM', $pin->pin_type);

        // Next PWM must fail because NodeMCU only has 1 PWM (D7)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/No empty PWM slot available/i');
        $this->engine->allocateAppliance($this->device, $dimmer, "Second Dimmer");
    }

    public function test_detach_frees_slot_for_reallocation(): void
    {
        $bulb = Appliance::create([
            'name' => 'LED Bulb',
            'type' => 'OUTPUT',
            'signal_type' => 'DIGITAL',
            'supported_pin_types' => ['DIGITAL'],
            'default_state' => '0',
            'status' => Appliance::STATUS_ACTIVE,
        ]);

        // Allocate
        $pin = $this->engine->allocateAppliance($this->device, $bulb, "Test Light");
        $this->assertTrue($pin->is_assigned);

        // Detach
        $this->engine->detachAppliance($pin);
        $pin->refresh();
        $this->assertFalse($pin->is_assigned);
        $this->assertNull($pin->appliance_id);

        // Check availability returns 4 free again
        $check = $this->engine->checkSlotAvailability($this->device, $bulb);
        $this->assertEquals(4, $check['free_count']);
    }

    public function test_toggle_state(): void
    {
        $bulb = Appliance::create([
            'name' => 'LED Bulb',
            'type' => 'OUTPUT',
            'signal_type' => 'DIGITAL',
            'supported_pin_types' => ['DIGITAL'],
            'default_state' => '0',
            'status' => Appliance::STATUS_ACTIVE,
        ]);

        $pin = $this->engine->allocateAppliance($this->device, $bulb, "Night Lamp");
        $this->assertEquals('0', $pin->current_state);

        // Turn ON
        $result = $this->engine->toggleState($pin);
        $this->assertEquals('1', $result['state']);
        $this->assertTrue($result['is_on']);

        // Turn OFF
        $result = $this->engine->toggleState($pin);
        $this->assertEquals('0', $result['state']);
        $this->assertFalse($result['is_on']);
    }
}
