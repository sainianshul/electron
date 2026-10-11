<?php

return [
    'host' => env('MQTT_HOST', 'mosquitto'),
    'port' => (int) env('MQTT_PORT', 1883),
    'client_id' => env('MQTT_CLIENT_ID', 'electronia_server'),
    'username' => env('MQTT_USERNAME', null),
    'password' => env('MQTT_PASSWORD', null),
    'clean_session' => true,
    'timeout' => (int) env('MQTT_TIMEOUT', 3), // 3 seconds timeout so web requests never freeze
    'qos' => (int) env('MQTT_QOS', 0),
    'retain' => (bool) env('MQTT_RETAIN', false),
];
