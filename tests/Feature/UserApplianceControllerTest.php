<?php

namespace Tests\Feature;

use App\Models\Appliance;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApplianceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Device $device;
    protected Appliance $fan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->device = Device::create([
            'user_id' => $this->user->id,
            'name' => 'Main Board',
            'mac_address' => 'AA:BB:CC:11:22:33',
            'status' => Device::STATUS_ACTIVE,
        ]);
        $this->device->createDefaultPins();

        $this->fan = Appliance::create([
            'name' => 'Ceiling Fan',
            'type' => 'OUTPUT',
            'signal_type' => 'DIGITAL',
            'supported_pin_types' => ['DIGITAL'],
            'default_state' => '0',
            'status' => Appliance::STATUS_ACTIVE,
        ]);
    }

    public function test_user_can_view_appliances_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('user.appliances.index'));

        $response->assertStatus(200);
        $response->assertSee('My Smart Appliances');
    }

    public function test_check_availability_endpoint(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('user.appliances.check-availability', [
            'device_id' => $this->device->id,
            'appliance_id' => $this->fan->id,
        ]));

        $response->assertStatus(200)
            ->assertJson([
                'available' => true,
                'free_count' => 4,
            ]);
    }

    public function test_user_can_attach_appliance_via_modal_store(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('user.appliances.store'), [
            'device_id' => $this->device->id,
            'appliance_id' => $this->fan->id,
            'custom_name' => 'Hall Fan',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('device_pins', [
            'device_id' => $this->device->id,
            'appliance_id' => $this->fan->id,
            'custom_name' => 'Hall Fan',
            'is_assigned' => true,
        ]);
    }

    public function test_toggle_endpoint_updates_state(): void
    {
        $pin = $this->device->pins()->first();
        $pin->update([
            'appliance_id' => $this->fan->id,
            'custom_name' => 'Hall Fan',
            'is_assigned' => true,
            'current_state' => '0',
        ]);

        $response = $this->actingAs($this->user)->postJson(route('user.appliances.toggle', $pin->id), [
            'state' => '1',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'state' => '1',
                'is_on' => true,
            ]);

        $this->assertEquals('1', $pin->fresh()->current_state);
    }

    public function test_detach_endpoint_frees_channel(): void
    {
        $pin = $this->device->pins()->first();
        $pin->update([
            'appliance_id' => $this->fan->id,
            'custom_name' => 'Hall Fan',
            'is_assigned' => true,
            'current_state' => '1',
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('user.appliances.detach', $pin->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertFalse((bool)$pin->fresh()->is_assigned);
        $this->assertNull($pin->fresh()->appliance_id);
    }
}
