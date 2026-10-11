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
    protected $signature = 'mqtt:test {--topic=electronia/test} {--message=ping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connectivity to the Mosquitto MQTT broker and optionally publish a test payload';

    /**
     * Execute the console command.
     */
    public function handle(MqttService $mqtt): int
    {
        $this->info("Testing connection to MQTT Broker at " . config('mqtt.host') . ":" . config('mqtt.port') . "...");

        $status = $mqtt->testConnection();

        if (!$status['connected']) {
            $this->error("Connection Failed: " . ($status['error'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info("Successfully connected to MQTT Broker!");

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
