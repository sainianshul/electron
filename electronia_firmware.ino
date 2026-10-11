/*
 * ==================================================================================
 *   ELECTRONIA SMART HOME - PRODUCTION FIRMWARE (ESP8266 / NodeMCU)
 * ==================================================================================
 *   Board     : NodeMCU 1.0 (ESP-12E Module)
 *   Broker    : Eclipse Mosquitto @ 20.235.244.153:1883
 *   Protocol  : MQTT v3.1.1 (PubSubClient)
 *   Features  : 
 *     - Auto-reconnect WiFi & MQTT
 *     - Sub-millisecond Relay Switch (Digital 0/1)
 *     - PWM Fan / Dimmer Control (0 to 255)
 *     - Dual-Topic Listener (Standard MAC & Dot MAC)
 *     - Built-in LED State Visualizer
 *     - LWT (Last Will) & 60s Telemetry Heartbeat
 * ==================================================================================
 */

#include <ESP8266WiFi.h>
#include <PubSubClient.h>

// ==================================================================================
// 1. NETWORK & BROKER CONFIGURATION
// ==================================================================================
const char* WIFI_SSID     = "3118_2.4G";
const char* WIFI_PASSWORD = "Secure@@3118";

const char* MQTT_SERVER   = "20.235.244.153";
const int   MQTT_PORT     = 1883;

// Hardware MAC Address of your NodeMCU Board
const char* DEVICE_MAC_COLON = "C8:C9:A3:34:F8:1E";
const char* DEVICE_MAC_DOT   = "C8.C9.A3.34.F8.1E";

// Relay Hardware Mode:
// Set to false for active-HIGH relays (relay turns ON when signal is HIGH)
const bool RELAY_ACTIVE_LOW  = false;

// Onboard Blue LED for Connection Feedback (GPIO 2 / D4)
const int LED_STATUS_PIN     = 2; // Active LOW on NodeMCU

// ==================================================================================
// 2. HARDWARE PIN DEFINITIONS (NodeMCU ESP-12E)
// ==================================================================================
struct PinDef {
    const char* pinName;
    uint8_t gpio;
};

// Maps friendly pin names ("D1", "D2", etc.) to actual ESP8266 GPIO numbers
const PinDef HARDWARE_PINS[] = {
    {"D1", 5},  // D1 -> GPIO 5
    {"D2", 4},  // D2 -> GPIO 4
    {"D3", 0},  // D3 -> GPIO 0
    {"D5", 14}, // D5 -> GPIO 14
    {"D6", 12}, // D6 -> GPIO 12
    {"D7", 13}, // D7 -> GPIO 13
    {"D8", 15}  // D8 -> GPIO 15
};
const int TOTAL_PINS = sizeof(HARDWARE_PINS) / sizeof(HARDWARE_PINS[0]);

WiFiClient espClient;
PubSubClient mqttClient(espClient);

unsigned long lastHeartbeatTime = 0;
unsigned long lastReconnectAttempt = 0;

// ==================================================================================
// 3. PIN RESOLVER HELPER
// ==================================================================================
int resolveGpio(String name) {
    name.toUpperCase();
    name.trim();
    for (int i = 0; i < TOTAL_PINS; i++) {
        if (name.equals(HARDWARE_PINS[i].pinName)) {
            return HARDWARE_PINS[i].gpio;
        }
    }
    return -1;
}

// ==================================================================================
// 4. LED FEEDBACK HELPER
// ==================================================================================
void blinkStatusLed(int count, int delayMs) {
    for (int i = 0; i < count; i++) {
        digitalWrite(LED_STATUS_PIN, LOW);  // LED ON
        delay(delayMs);
        digitalWrite(LED_STATUS_PIN, HIGH); // LED OFF
        delay(delayMs);
    }
}

// ==================================================================================
// 5. INCOMING MQTT MESSAGE HANDLER (WEB TOGGLE / SLIDER)
// ==================================================================================
void onMqttMessageReceived(char* topic, byte* payload, unsigned int length) {
    // Null terminate payload buffer safely
    char msgBuffer[length + 1];
    memcpy(msgBuffer, payload, length);
    msgBuffer[length] = '\0';

    String topicStr = String(topic);
    String message = String(msgBuffer);
    message.trim();

    Serial.println("\n--------------------------------------------------");
    Serial.printf("[MQTT IN] Topic: %s\n", topicStr.c_str());
    Serial.printf("[PAYLOAD] State: %s\n", message.c_str());

    // Instant blink to show command received
    digitalWrite(LED_STATUS_PIN, LOW);

    // Target pattern: devices/{MAC}/pin/{PIN}/set
    // Examples:
    //   devices/C8:C9:A3:34:F8:1E/pin/D1/set
    //   devices/C8.C9.A3.34.F8.1E/pin/D1/set
    if (topicStr.endsWith("/set")) {
        int pinIdx = topicStr.indexOf("/pin/");
        if (pinIdx != -1) {
            int setIdx = topicStr.lastIndexOf("/set");
            String pinName = topicStr.substring(pinIdx + 5, setIdx);
            pinName.toUpperCase();

            int gpio = resolveGpio(pinName);

            if (gpio != -1) {
                int value = message.toInt();

                // CASE A: Standard Digital Relay (0 = OFF, 1 = ON)
                if (message == "0" || message == "1") {
                    int outputLevel;
                    if (RELAY_ACTIVE_LOW) {
                        outputLevel = (value == 1) ? LOW : HIGH;
                    } else {
                        outputLevel = (value == 1) ? HIGH : LOW;
                    }

                    digitalWrite(gpio, outputLevel);
                    Serial.printf("[RELAY] Pin %s (GPIO %d) set to %s (%s)\n",
                                  pinName.c_str(), gpio, (value == 1 ? "ON [1]" : "OFF [0]"),
                                  (outputLevel == LOW ? "LOW" : "HIGH"));
                }
                // CASE B: PWM Dimmer / Fan Speed (0 to 255)
                else if (value >= 0 && value <= 255) {
                    analogWrite(gpio, value);
                    int pct = map(value, 0, 255, 0, 100);
                    Serial.printf("[PWM] Pin %s (GPIO %d) set to %d / 255 (~%d%%)\n",
                                  pinName.c_str(), gpio, value, pct);
                }

                // Send Acknowledge State back to MQTT Broker
                String ackTopic = "devices/" + String(DEVICE_MAC_COLON) + "/pin/" + pinName + "/state";
                mqttClient.publish(ackTopic.c_str(), message.c_str(), true);
                Serial.printf("[ACK SENT] %s => %s\n", ackTopic.c_str(), message.c_str());

            } else {
                Serial.printf("[WARNING] Unknown pin requested: %s\n", pinName.c_str());
            }
        }
    }

    digitalWrite(LED_STATUS_PIN, HIGH);
    Serial.println("--------------------------------------------------");
}

// ==================================================================================
// 6. MQTT BROKER CONNECTION & SUBSCRIPTION
// ==================================================================================
boolean connectToMqtt() {
    Serial.print("Connecting to MQTT Broker (");
    Serial.print(MQTT_SERVER);
    Serial.print(")... ");

    String clientId = "ESP8266-" + String(ESP.getChipId(), HEX);
    String lwtTopic = "devices/" + String(DEVICE_MAC_COLON) + "/status";

    // Last Will & Testament: If board disconnects or power is cut, broker publishes "offline"
    boolean connected = mqttClient.connect(
        clientId.c_str(),
        lwtTopic.c_str(),
        1,
        true,
        "offline"
    );

    if (connected) {
        Serial.println("CONNECTED! [OK]");

        // 1. Subscribe with colon format: devices/C8:C9:A3:34:F8:1E/pin/+/set
        String subColon = "devices/" + String(DEVICE_MAC_COLON) + "/pin/+/set";
        mqttClient.subscribe(subColon.c_str());
        Serial.printf("[SUB] Subscribed: %s\n", subColon.c_str());

        // 2. Subscribe with dot format: devices/C8.C9.A3.34.F8.1E/pin/+/set
        String subDot = "devices/" + String(DEVICE_MAC_DOT) + "/pin/+/set";
        mqttClient.subscribe(subDot.c_str());
        Serial.printf("[SUB] Subscribed: %s\n", subDot.c_str());

        // 3. Mark device online
        mqttClient.publish(lwtTopic.c_str(), "online", true);

        // Success 3 quick blinks
        blinkStatusLed(3, 80);
        return true;
    } else {
        Serial.printf("Failed (rc=%d). Will retry.\n", mqttClient.state());
        return false;
    }
}

// ==================================================================================
// 7. ARDUINO SETUP
// ==================================================================================
void setup() {
    Serial.begin(115200);
    delay(250);

    Serial.println("\n\n");
    Serial.println("==================================================");
    Serial.println("   ELECTRONIA SMART HOME SYSTEM - ESP8266   ");
    Serial.println("==================================================");
    Serial.printf("Hardware MAC : %s\n", WiFi.macAddress().c_str());
    Serial.printf("Config MAC   : %s / %s\n", DEVICE_MAC_COLON, DEVICE_MAC_DOT);
    Serial.printf("Target WiFi  : %s\n", WIFI_SSID);
    Serial.printf("MQTT Server  : %s:%d\n", MQTT_SERVER, MQTT_PORT);
    Serial.println("==================================================");

    // Initialize Status LED
    pinMode(LED_STATUS_PIN, OUTPUT);
    digitalWrite(LED_STATUS_PIN, HIGH); // OFF

    // Initialize all relay pins as OUTPUT in safe OFF state
    for (int i = 0; i < TOTAL_PINS; i++) {
        pinMode(HARDWARE_PINS[i].gpio, OUTPUT);
        digitalWrite(HARDWARE_PINS[i].gpio, RELAY_ACTIVE_LOW ? HIGH : LOW);
        Serial.printf("Initialized Pin %s (GPIO %d) -> OFF\n", 
                      HARDWARE_PINS[i].pinName, HARDWARE_PINS[i].gpio);
    }

    // Connect to WiFi
    Serial.printf("\nConnecting to WiFi '%s' ", WIFI_SSID);
    WiFi.mode(WIFI_STA);
    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int wifiAttempts = 0;
    while (WiFi.status() != WL_CONNECTED) {
        delay(400);
        digitalWrite(LED_STATUS_PIN, !digitalRead(LED_STATUS_PIN)); // Blink
        Serial.print(".");
        wifiAttempts++;
        if (wifiAttempts > 60) {
            Serial.println("\n[ERROR] Unable to connect to WiFi! Checking credentials...");
            wifiAttempts = 0;
        }
    }

    digitalWrite(LED_STATUS_PIN, HIGH); // Turn LED OFF
    Serial.println("\nWiFi Connected Successfully! [OK]");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("Signal (RSSI): ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm\n");

    // Configure MQTT Client
    mqttClient.setServer(MQTT_SERVER, MQTT_PORT);
    mqttClient.setCallback(onMqttMessageReceived);
    mqttClient.setBufferSize(512); // Buffer size for JSON & topics
    mqttClient.setKeepAlive(15);

    // Initial MQTT Connect
    connectToMqtt();
}

// ==================================================================================
// 8. ARDUINO MAIN LOOP
// ==================================================================================
void loop() {
    // 1. Maintain WiFi connection
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi connection lost! Reconnecting...");
        WiFi.reconnect();
        delay(1000);
        return;
    }

    // 2. Maintain MQTT connection
    if (!mqttClient.connected()) {
        unsigned long now = millis();
        if (now - lastReconnectAttempt > 4000) {
            lastReconnectAttempt = now;
            if (connectToMqtt()) {
                lastReconnectAttempt = 0;
            }
        }
    } else {
        // Client is connected, process MQTT events
        mqttClient.loop();
    }

    // 3. Periodic Telemetry Heartbeat (Every 60 Seconds)
    unsigned long now = millis();
    if (now - lastHeartbeatTime >= 60000) {
        lastHeartbeatTime = now;
        if (mqttClient.connected()) {
            String hbTopic = "devices/" + String(DEVICE_MAC_COLON) + "/heartbeat";
            String payload = "{\"uptime\":" + String(now / 1000) + 
                             ",\"rssi\":" + String(WiFi.RSSI()) + 
                             ",\"free_ram\":" + String(ESP.getFreeHeap()) + "}";

            mqttClient.publish(hbTopic.c_str(), payload.c_str());
            Serial.printf("[HEARTBEAT] Published: %s\n", payload.c_str());
        }
    }
}
