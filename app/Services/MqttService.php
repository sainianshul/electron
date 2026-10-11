<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DevicePin;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttService
{
    protected string $host;
    protected int $port;
    protected string $clientId;
    protected ?string $username;
    protected ?string $password;
    protected int $timeout;

    public function __construct()
    {
        $this->host = config('mqtt.host', 'mosquitto');
        $this->port = (int) config('mqtt.port', 1883);
        $this->clientId = config('mqtt.client_id', 'electronia_server') . '_' . substr(uniqid(), -6);
        $this->username = config('mqtt.username');
        $this->password = config('mqtt.password');
        $this->timeout = (int) config('mqtt.timeout', 3);
    }

    /**
     * Publish a state change to the MQTT broker for a hardware pin.
     *
     * @param DevicePin $pin
     * @param string $state
     * @return array
     */
    public function publishPinState(DevicePin $pin, string $state): array
    {
        $device = $pin->device;
        if (!$device) {
            return [
                'published' => false,
                'message' => 'Device not associated with pin.',
            ];
        }

        // Clean MAC address (e.g., 12.34.34.56 or AA:BB:CC:DD:EE:FF -> uppercase without colons or standard)
        $cleanMac = strtoupper(str_replace([':', '-', ' '], '', $device->mac_address));
        $rawMac = $device->mac_address;

        // Resolve physical hardware payload:
        // Hardware boards with Active-LOW relay energize ON with payload '0' and turn OFF with '1'.
        // Inverting for digital pins ensures that Dashboard '1' (ON) turns relay ON, and '0' (OFF) turns relay OFF.
        $hardwareState = $state;
        if ($pin->pin_type !== 'PWM') {
            if ($state === '1') {
                $hardwareState = '0';
            } elseif ($state === '0') {
                $hardwareState = '1';
            }
        }

        // Topics:
        // 1. Direct Pin topic: devices/{mac}/pin/{pin_name}/set
        $pinTopic = "devices/{$rawMac}/pin/{$pin->pin_name}/set";

        // 2. Structured JSON command: devices/{mac}/command
        $cmdTopic = "devices/{$rawMac}/command";
        $payload = json_encode([
            'pin' => $pin->pin_name,
            'gpio' => $pin->gpio_number,
            'state' => $hardwareState,
            'ui_state' => $state,
            'appliance' => $pin->custom_name ?: ($pin->appliance->name ?? null),
            'timestamp' => time(),
        ]);

        try {
            $client = $this->createClient();
            $client->connect($this->getConnectionSettings(), true);

            // Publish to both topics for maximum firmware compatibility
            $client->publish($pinTopic, $hardwareState, 0);
            $client->publish($cmdTopic, $payload, 0);

            // Also publish to cleanMac topic in case firmware uses unpunctuated MAC
            if ($cleanMac !== $rawMac) {
                $client->publish("devices/{$cleanMac}/pin/{$pin->pin_name}/set", $hardwareState, 0);
                $client->publish("devices/{$cleanMac}/command", $payload, 0);
            }

            $client->disconnect();

            return [
                'published' => true,
                'pin_topic' => $pinTopic,
                'command_topic' => $cmdTopic,
                'payload' => $hardwareState,
                'ui_state' => $state,
                'message' => 'MQTT command published successfully.',
            ];

        } catch (Exception $e) {
            Log::warning("MQTT Publish Failed for {$device->name} ({$pin->pin_name}): " . $e->getMessage());

            return [
                'published' => false,
                'error' => $e->getMessage(),
                'message' => 'MQTT broker unreachable or timed out.',
            ];
        }
    }

    /**
     * Publish raw message to a specific topic.
     *
     * @param string $topic
     * @param string $message
     * @param int $qos
     * @param bool $retain
     * @return bool
     */
    public function publish(string $topic, string $message, int $qos = 0, bool $retain = false): bool
    {
        try {
            $client = $this->createClient();
            $client->connect($this->getConnectionSettings(), true);
            $client->publish($topic, $message, $qos, $retain);
            $client->disconnect();
            return true;
        } catch (Exception $e) {
            Log::warning("MQTT Publish to [{$topic}] Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test connection to the MQTT broker.
     *
     * @return array
     */
    public function testConnection(): array
    {
        try {
            $client = $this->createClient();
            $client->connect($this->getConnectionSettings(), true);
            $client->disconnect();

            return [
                'connected' => true,
                'host' => $this->host,
                'port' => $this->port,
                'message' => 'Connected to MQTT broker successfully!',
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'host' => $this->host,
                'port' => $this->port,
                'error' => $e->getMessage(),
                'message' => 'Unable to connect to MQTT broker.',
            ];
        }
    }

    /**
     * Create an MQTT client instance.
     *
     * @return MqttClient
     */
    protected function createClient(): MqttClient
    {
        return new MqttClient($this->host, $this->port, $this->clientId);
    }

    /**
     * Build connection settings.
     *
     * @return ConnectionSettings
     */
    protected function getConnectionSettings(): ConnectionSettings
    {
        $settings = (new ConnectionSettings())
            ->setConnectTimeout($this->timeout)
            ->setSocketTimeout($this->timeout)
            ->setKeepAliveInterval(10);

        if (!empty($this->username)) {
            $settings->setUsername($this->username);
            if (!empty($this->password)) {
                $settings->setPassword($this->password);
            }
        }

        return $settings;
    }
}
