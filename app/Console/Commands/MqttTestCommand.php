<?php

namespace App\Console\Commands;

use App\Services\MqttService;
use Illuminate\Console\Command;

class MqttTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:test {--topic=electronia/test} {--message=ping} {--pin=} {--state=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connectivity to the Mosquitto MQTT broker and test pin toggling';

    /**
     * Execute the console command.
     */
    public function handle(MqttService $mqtt, \App\Services\ApplianceEngine $engine): int
    {
        $this->info("Testing connection to MQTT Broker at " . config('mqtt.host') . ":" . config('mqtt.port') . "...");

        $status = $mqtt->testConnection();

        if (!$status['connected']) {
            $this->error("Connection Failed: " . ($status['error'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info("Successfully connected to MQTT Broker!");

        // If --pin is specified, test toggling an actual pin
        $pinId = $this->option('pin');
        if ($pinId) {
            $pin = \App\Models\DevicePin::with(['device', 'appliance'])->find($pinId);
            if (!$pin) {
                $this->error("DevicePin with ID {$pinId} not found!");
                return Command::FAILURE;
            }

            $state = $this->option('state');
            $this->line("Toggling Pin ID {$pin->id} ({$pin->pin_name}) on Device '{$pin->device->name}' (MAC: {$pin->device->mac_address})...");
            $result = $engine->toggleState($pin, $state);

            $this->table(
                ['Field', 'Value'],
                [
                    ['Pin ID', $result['pin_id']],
                    ['Appliance', $result['appliance_name']],
                    ['New State', $result['state']],
                    ['Is ON', $result['is_on'] ? 'YES' : 'NO'],
                    ['MQTT Published', ($result['mqtt']['published'] ?? false) ? 'YES' : 'NO'],
                    ['MQTT Pin Topic', $result['mqtt']['pin_topic'] ?? 'N/A'],
                    ['MQTT Cmd Topic', $result['mqtt']['command_topic'] ?? 'N/A'],
                ]
            );

            return Command::SUCCESS;
        }

        $topic = (string) $this->option('topic');
        $message = (string) $this->option('message');

        $this->line("Publishing test message to [{$topic}]: {$message}");
        $published = $mqtt->publish($topic, $message);

        if ($published) {
            $this->info("Message published successfully!");
            return Command::SUCCESS;
        }

        $this->error("Failed to publish message.");
        return Command::FAILURE;
    }
}
