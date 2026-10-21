@extends('admin.layouts.app')

@section('title', 'AI Finger & Gesture Control — Electronia')

@push('styles')
<!-- MediaPipe & Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --gesture-bg-card: rgba(18, 25, 38, 0.75);
        --gesture-cyan: #38BDF8;
        --gesture-green: #10B981;
        --gesture-amber: #F59E0B;
        --gesture-purple: #A855F7;
    }

    .gesture-header-card {
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.08) 0%, rgba(168, 85, 247, 0.05) 100%);
        border: 1px solid rgba(56, 189, 248, 0.2);
        padding: 20px 24px;
        margin-bottom: 24px;
    }

    .gesture-card {
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: var(--tblr-card-bg, #ffffff);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        margin-bottom: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    /* Dark Mode Glass Styling */
    [data-bs-theme="dark"] .gesture-card {
        background: rgba(18, 25, 38, 0.85);
        border-color: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
    }

    /* Camera Viewport */
    .camera-container {
        position: relative;
        width: 100%;
        border-radius: 16px;
        overflow: hidden;
        background: #0B0F17;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
        aspect-ratio: 4 / 3;
    }

    #webcam {
        display: none;
    }

    #output_canvas {
        width: 100%;
        height: 100%;
        display: block;
        transform: scaleX(-1); /* Default mirrored */
    }

    .cam-hud-top {
        position: absolute;
        top: 12px;
        left: 12px;
        right: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        pointer-events: none;
    }

    .hud-chip {
        background: rgba(11, 15, 23, 0.8);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #F8FAFC;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .hud-chip.fps {
        font-family: 'JetBrains Mono', monospace;
        color: var(--gesture-cyan);
    }

    .hud-chip.active-gesture {
        color: #34D399;
        border-color: rgba(16, 185, 129, 0.4);
    }

    .cam-hud-bottom {
        position: absolute;
        bottom: 12px;
        left: 12px;
        right: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        pointer-events: none;
    }

    .mqtt-stream-badge {
        background: rgba(11, 15, 23, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.3);
        color: #E2E8F0;
        padding: 4px 12px;
        border-radius: 12px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px;
    }

    /* Active Finger Hero Box */
    .finger-hero-card {
        text-align: center;
        padding: 24px;
        background: linear-gradient(180deg, rgba(56, 189, 248, 0.08) 0%, transparent 100%);
        border: 1.5px solid rgba(56, 189, 248, 0.3);
        border-radius: 18px;
    }

    .finger-big-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 72px;
        font-weight: 800;
        line-height: 1;
        color: #38BDF8;
        text-shadow: 0 0 25px rgba(56, 189, 248, 0.5);
        margin: 10px 0;
        transition: transform 0.15s ease;
    }

    .finger-big-num.pulse {
        transform: scale(1.1);
        color: #10B981;
        text-shadow: 0 0 35px rgba(16, 185, 129, 0.8);
    }

    /* Pin Mapping Cards */
    .pin-mapping-item {
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        padding: 14px 16px;
        margin-bottom: 12px;
        background: rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: all 0.2s ease;
    }

    [data-bs-theme="dark"] .pin-mapping-item {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.06);
    }

    .pin-mapping-item.is-firing {
        border-color: #10B981 !important;
        background: rgba(16, 185, 129, 0.12) !important;
        box-shadow: 0 0 20px rgba(16, 185, 129, 0.25);
        transform: translateX(4px);
    }

    .pin-badge-code {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        font-size: 13px;
        padding: 4px 8px;
        border-radius: 6px;
        background: rgba(56, 189, 248, 0.12);
        color: #0284c7;
        border: 1px solid rgba(56, 189, 248, 0.3);
    }

    [data-bs-theme="dark"] .pin-badge-code {
        color: #38BDF8;
    }

    .gesture-trigger-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        background: rgba(168, 85, 247, 0.12);
        color: #9333ea;
        border: 1px solid rgba(168, 85, 247, 0.3);
    }

    [data-bs-theme="dark"] .gesture-trigger-pill {
        color: #C084FC;
    }

    /* Dimmer Gauge */
    .dimmer-track {
        height: 14px;
        background: rgba(0, 0, 0, 0.08);
        border-radius: 20px;
        overflow: hidden;
        position: relative;
    }
    [data-bs-theme="dark"] .dimmer-track {
        background: rgba(255, 255, 255, 0.08);
    }
    .dimmer-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #F59E0B 0%, #EF4444 100%);
        border-radius: 20px;
        transition: width 0.08s ease;
        box-shadow: 0 0 12px rgba(245, 158, 11, 0.6);
    }
</style>
@endpush

@section('content')
<div class="container-xl">

    <!-- Header Section -->
    <div class="gesture-header-card d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-lt fw-bold fs-7">
                    <i class="ti ti-sparkles me-1"></i> AI Computer Vision
                </span>
                <span class="badge bg-green-lt fw-bold fs-7" id="mqttStatusBadge">
                    <i class="ti ti-wifi me-1"></i> MQTT: Connecting...
                </span>
            </div>
            <h2 class="mb-1 fw-bold text-reset">⚡ AI Finger & Gesture Control</h2>
            <p class="text-muted mb-0 fs-6">Apne haath ki ungliyon aur gesture se hardware pins aur appliances ko live switch ya dim karein.</p>
        </div>

        <!-- Device Selector -->
        <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 fw-bold text-nowrap"><i class="ti ti-cpu me-1"></i> Target Device:</label>
            <select id="deviceSelect" class="form-select w-auto fw-bold" style="min-width: 220px;">
                @forelse($devices as $dev)
                    <option value="{{ $dev->id }}" data-mac="{{ $dev->mac_address }}" {{ $loop->first ? 'selected' : '' }}>
                        {{ $dev->name }} ({{ $dev->mac_address }})
                    </option>
                @empty
                    <option value="" disabled>No devices found</option>
                @endforelse
            </select>
        </div>
    </div>

    <!-- Main Workspace Grid -->
    <div class="row row-deck row-cards">
        
        <!-- Left: Live Camera Viewport & Dimmer -->
        <div class="col-lg-6 col-md-12">
            <div class="gesture-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-bold fs-5">
                        <i class="ti ti-camera me-1 text-primary"></i> Live Optical Feed
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" id="btnToggleMirror" title="Mirror/Flip Camera">
                            <i class="ti ti-flip-horizontal"></i> Flip
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="btnToggleCam">
                            <i class="ti ti-player-pause"></i> Pause
                        </button>
                    </div>
                </div>

                <!-- Video / Canvas -->
                <div class="camera-container">
                    <video id="webcam" playsinline></video>
                    <canvas id="output_canvas" width="640" height="480"></canvas>

                    <!-- Top HUD -->
                    <div class="cam-hud-top">
                        <span class="hud-chip active-gesture" id="hudActiveGesture">
                            <i class="ti ti-hand-stop"></i> Ready
                        </span>
                        <span class="hud-chip fps" id="hudFps">0 FPS</span>
                    </div>

                    <!-- Bottom HUD -->
                    <div class="cam-hud-bottom">
                        <span class="mqtt-stream-badge" id="hudMqttLog">📡 Waiting for gesture...</span>
                    </div>
                </div>

                <!-- Live Pinch Dimmer Slider (Thumb + Finger Distance) -->
                <div class="mt-3 p-3 rounded-3" style="background: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold fs-6 text-warning">
                            <i class="ti ti-adjustments me-1"></i> 🤏 Dimmer PWM (Thumb & Index Pinch)
                        </span>
                        <span class="font-monospace fw-bold text-warning fs-6" id="dimmerValueLabel">0% (0 / 255)</span>
                    </div>
                    <div class="dimmer-track mb-2">
                        <div class="dimmer-fill" id="dimmerFillBar"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted fs-8">
                        <span>Chutki Band = 0 (OFF)</span>
                        <span id="pwmTargetPinText">Target: D7 (PWM Pin)</span>
                        <span>Chutki Door = 255 (Full)</span>
                    </div>
                </div>

                <!-- Quick Help -->
                <div class="mt-2 text-muted fs-8 text-center">
                    <i class="ti ti-info-circle me-1"></i> Tip: Smooth Anti-Jitter enabled hai. Ungli ko ~0.2s camera ke aage pakad kar rakhein.
                </div>
            </div>
        </div>

        <!-- Right: Active Gesture Hero & Pin Mapping Dashboard -->
        <div class="col-lg-6 col-md-12">
            
            <!-- Hero Detected Action -->
            <div class="gesture-card p-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-sm-5 text-center border-end">
                        <div class="text-uppercase text-muted fs-8 fw-bold tracking-wider">Detected Fingers</div>
                        <div class="finger-big-num" id="heroFingerNum">0</div>
                        <div class="fw-bold fs-6 text-reset" id="heroGestureTitle">✊ Fist (All Closed)</div>
                    </div>
                    <div class="col-sm-7 ps-sm-4 pt-3 pt-sm-0">
                        <div class="text-uppercase text-muted fs-8 fw-bold mb-2">Live Hardware Action</div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-green text-white p-2" id="actionStatusIcon">
                                <i class="ti ti-check fs-4"></i>
                            </span>
                            <div>
                                <div class="fw-bold fs-5" id="heroActionSummary">System Idle</div>
                                <div class="text-muted fs-7" id="heroActionDetail">Apna haath camera ke samne dikhayein.</div>
                            </div>
                        </div>

                        <!-- Sensitivity Setting -->
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <span class="text-muted fs-7"><i class="ti ti-shield-check me-1"></i> Stability:</span>
                            <span class="badge bg-blue-lt">Anti-Flood Active (5 Frames Lock)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pin to Gesture Configuration Card -->
            <div class="gesture-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-bold fs-5"><i class="ti ti-circuit-switch me-1 text-green"></i> Device Channels & Trigger Mapping</div>
                        <div class="text-muted fs-7">Har pin kis gesture/finger par trigger hoga:</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" id="btnRefreshDevice">
                        <i class="ti ti-refresh"></i> Sync State
                    </button>
                </div>

                <!-- Dynamic Pin List Container -->
                <div id="pinsListContainer">
                    <!-- Populated dynamically via JS from $devices -->
                </div>

                <!-- Master Rule Guide -->
                <div class="alert alert-info d-flex align-items-center mb-0 mt-3 p-2" role="alert">
                    <i class="ti ti-info-circle fs-2 me-2"></i>
                    <div class="fs-7">
                        <strong>Default Rules:</strong> <b>1 Finger</b> = Pin 1 ON | <b>2 Fingers</b> = Pin 2 ON | <b>3 Fingers</b> = Pin 3 ON | <b>4 Fingers</b> = Pin 4 ON | <b>0 Fingers (Fist)</b> = All Channels OFF.
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<!-- MediaPipe Hands & Camera JS -->
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>

<!-- MQTT.js for Browser-to-Docker WebSocket -->
<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

<script>
    // =========================================================================
    // 1. DATA INITIALIZATION FROM LARAVEL BACKEND
    // =========================================================================
    const ALL_DEVICES = @json($devices);
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const deviceSelect = document.getElementById('deviceSelect');
    const pinsListContainer = document.getElementById('pinsListContainer');
    const mqttStatusBadge = document.getElementById('mqttStatusBadge');
    const hudMqttLog = document.getElementById('hudMqttLog');
    const hudActiveGesture = document.getElementById('hudActiveGesture');
    const heroFingerNum = document.getElementById('heroFingerNum');
    const heroGestureTitle = document.getElementById('heroGestureTitle');
    const heroActionSummary = document.getElementById('heroActionSummary');
    const heroActionDetail = document.getElementById('heroActionDetail');
    const dimmerFillBar = document.getElementById('dimmerFillBar');
    const dimmerValueLabel = document.getElementById('dimmerValueLabel');
    const pwmTargetPinText = document.getElementById('pwmTargetPinText');
    const hudFps = document.getElementById('hudFps');

    let activeDevice = null;
    let pinFingerMap = {}; // Key: fingerCount (1-5), Value: pin object
    let pwmPin = null;

    // =========================================================================
    // 2. MQTT WEBSOCKET CONNECTION
    // =========================================================================
    // Priority: ws://localhost:9001 (Docker) -> wss://electron.rezel.online/mqtt
    const MQTT_WS_URL = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? 'ws://' + window.location.hostname + ':9001'
        : 'wss://electron.rezel.online/mqtt';

    let mqttClient = null;

    function initMQTT() {
        try {
            const clientId = 'gesture_web_' + Math.random().toString(16).substring(2, 8);
            mqttClient = mqtt.connect(MQTT_WS_URL, {
                clientId: clientId,
                clean: true,
                connectTimeout: 5000,
                reconnectPeriod: 3000
            });

            mqttClient.on('connect', () => {
                mqttStatusBadge.className = 'badge bg-green-lt fw-bold fs-7';
                mqttStatusBadge.innerHTML = '<i class="ti ti-wifi me-1"></i> MQTT: Connected (:9001)';
                console.log("Connected to MQTT Broker via WebSocket");
            });

            mqttClient.on('error', (err) => {
                mqttStatusBadge.className = 'badge bg-yellow-lt fw-bold fs-7';
                mqttStatusBadge.innerHTML = '<i class="ti ti-alert-circle me-1"></i> MQTT: WS Offline (API Fallback)';
            });

            mqttClient.on('close', () => {
                mqttStatusBadge.className = 'badge bg-secondary-lt fw-bold fs-7';
                mqttStatusBadge.innerHTML = '<i class="ti ti-wifi-off me-1"></i> MQTT: Reconnecting...';
            });
        } catch (e) {
            console.warn("MQTT WebSockets init error:", e);
        }
    }

    initMQTT();

    // =========================================================================
    // 3. LOAD SELECTED DEVICE & CHANNELS
    // =========================================================================
    function loadSelectedDevice() {
        const selectedId = parseInt(deviceSelect.value);
        activeDevice = ALL_DEVICES.find(d => d.id === selectedId) || ALL_DEVICES[0];

        if (!activeDevice || !activeDevice.pins) {
            pinsListContainer.innerHTML = '<div class="text-muted p-3">No pins found on this device.</div>';
            return;
        }

        pinFingerMap = {};
        pwmPin = null;

        let html = '';
        let digitalCounter = 1;

        activeDevice.pins.forEach((pin, index) => {
            const isPwm = pin.pin_type === 'PWM';
            let assignedGesture = '';

            if (isPwm) {
                pwmPin = pin;
                assignedGesture = '🤏 Pinch (Dimmer)';
            } else {
                if (digitalCounter <= 5) {
                    pinFingerMap[digitalCounter] = pin;
                    assignedGesture = `${digitalCounter} Finger${digitalCounter > 1 ? 's' : ''}`;
                    digitalCounter++;
                } else {
                    assignedGesture = 'Unmapped';
                }
            }

            const appName = pin.custom_name || (pin.appliance ? pin.appliance.name : 'Unassigned Channel');
            const isOn = isPwm ? (parseInt(pin.current_state) > 0) : (pin.current_state === '1');

            html += `
                <div class="pin-mapping-item" id="pin_card_${pin.id}">
                    <div class="d-flex align-items-center gap-3">
                        <span class="pin-badge-code">${pin.pin_name}</span>
                        <div>
                            <div class="fw-bold fs-6 mb-0">${appName}</div>
                            <div class="text-muted fs-8">GPIO ${pin.gpio_number} • ${pin.pin_type}</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span class="gesture-trigger-pill">
                            <i class="ti ti-sparkles"></i> ${assignedGesture}
                        </span>

                        <span class="badge ${isOn ? 'bg-success' : 'bg-secondary'}" id="pin_badge_${pin.id}">
                            ${isOn ? (isPwm ? pin.current_state + ' PWM' : 'ON') : 'OFF'}
                        </span>

                        <button class="btn btn-sm btn-outline-secondary" onclick="manualTogglePin(${activeDevice.id}, ${pin.id})">
                            <i class="ti ti-power"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        pinsListContainer.innerHTML = html;

        if (pwmPin) {
            pwmTargetPinText.innerText = `Target: ${pwmPin.pin_name} (${pwmPin.custom_name || 'Dimmer'})`;
        } else {
            pwmTargetPinText.innerText = `No PWM Pin on this device`;
        }
    }

    deviceSelect.addEventListener('change', loadSelectedDevice);
    document.getElementById('btnRefreshDevice').addEventListener('click', loadSelectedDevice);
    loadSelectedDevice();

    // =========================================================================
    // 4. HARDWARE EXECUTION ENGINE (MQTT + API FALLBACK)
    // =========================================================================
    async function triggerPinHardware(pin, targetState, label) {
        if (!activeDevice || !pin) return;

        // Visual flash on card
        const cardEl = document.getElementById(`pin_card_${pin.id}`);
        if (cardEl) {
            cardEl.classList.add('is-firing');
            setTimeout(() => cardEl.classList.remove('is-firing'), 600);
        }

        const badgeEl = document.getElementById(`pin_badge_${pin.id}`);
        const isOn = (pin.pin_type === 'PWM') ? (parseInt(targetState) > 0) : (targetState === '1');
        if (badgeEl) {
            badgeEl.className = isOn ? 'badge bg-success' : 'badge bg-secondary';
            badgeEl.innerText = isOn ? (pin.pin_type === 'PWM' ? targetState + ' PWM' : 'ON') : 'OFF';
        }

        // 1. MQTT Direct Publish (Sub-millisecond latency to Docker Mosquitto)
        if (mqttClient && mqttClient.connected) {
            const rawMac = activeDevice.mac_address;
            const topic = `devices/${rawMac}/pin/${pin.pin_name}/set`;
            mqttClient.publish(topic, String(targetState));
            hudMqttLog.innerText = `📡 [MQTT] ${pin.pin_name} -> ${targetState}`;
        }

        // 2. Background Sync with Laravel Database via API
        try {
            const response = await fetch(`/devices/${activeDevice.id}/pins/${pin.id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ state: String(targetState) })
            });
            const data = await response.json();
            console.log("API Sync response:", data);
        } catch (e) {
            console.warn("Background API Sync error:", e);
        }
    }

    window.manualTogglePin = function(deviceId, pinId) {
        const pin = activeDevice.pins.find(p => p.id === pinId);
        if (!pin) return;
        const nextState = pin.current_state === '1' ? '0' : '1';
        pin.current_state = nextState;
        triggerPinHardware(pin, nextState, pin.custom_name);
    };

    // =========================================================================
    // 5. SMOOTHING, ANTI-FLOOD & MEDIAPIPE AI VISION LOGIC
    // =========================================================================
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('output_canvas');
    const canvasCtx = canvasElement.getContext('2d');

    // Anti-Flood / Debounce Variables
    let consecutiveFrames = 0;
    let candidateFingerCount = -1;
    let confirmedFingerCount = -1;
    const CONFIRMATION_THRESHOLD = 5; // Must be stable for 5 frames (~100-150ms)

    // PWM Dimmer Throttling & EMA Smoothing
    let emaPinch = 0;
    let lastPwmSent = -1;
    let lastPwmSentTime = 0;
    const PWM_THROTTLE_MS = 180; // Max ~5 updates per second

    // FPS Counter
    let lastFpsTime = performance.now();
    let frameCounter = 0;

    const GESTURE_TITLES = {
        0: "✊ Fist (All Channels OFF)",
        1: "☝️ 1 Finger (Pin 1)",
        2: "✌️ 2 Fingers (Pin 2)",
        3: "🤟 3 Fingers (Pin 3)",
        4: "🖖 4 Fingers (Pin 4)",
        5: "🖐️ 5 Fingers (Full Power)"
    };

    function onResults(results) {
        // Calculate FPS
        frameCounter++;
        const now = performance.now();
        if (now - lastFpsTime >= 1000) {
            hudFps.innerText = `${frameCounter} FPS`;
            frameCounter = 0;
            lastFpsTime = now;
        }

        // Draw video frame to canvas
        canvasCtx.save();
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);

        let detectedFingers = 0;
        let pinchRaw = 0;

        if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
            const landmarks = results.multiHandLandmarks[0];

            // Draw Hand Skeleton
            drawConnectors(canvasCtx, landmarks, HAND_CONNECTIONS, { color: '#00FFCC', lineWidth: 3 });
            drawLandmarks(canvasCtx, landmarks, { color: '#FF0055', lineWidth: 1, radius: 4 });

            // 1. Thumb Check (Robust distance check to pinky MCP landmark 17)
            const dTipPinky = Math.hypot(landmarks[4].x - landmarks[17].x, landmarks[4].y - landmarks[17].y);
            const dIpPinky = Math.hypot(landmarks[3].x - landmarks[17].x, landmarks[3].y - landmarks[17].y);
            const isThumbUp = (dTipPinky > dIpPinky * 1.18);

            // 2. Remaining 4 fingers (Tip Y < Joint PIP Y)
            const isIndexUp = (landmarks[8].y < landmarks[6].y);
            const isMiddleUp = (landmarks[12].y < landmarks[10].y);
            const isRingUp = (landmarks[16].y < landmarks[14].y);
            const isPinkyUp = (landmarks[20].y < landmarks[18].y);

            detectedFingers = (isThumbUp ? 1 : 0) + 
                              (isIndexUp ? 1 : 0) + 
                              (isMiddleUp ? 1 : 0) + 
                              (isRingUp ? 1 : 0) + 
                              (isPinkyUp ? 1 : 0);

            // 3. Pinch Distance Calculation (Thumb Tip 4 & Index Tip 8)
            pinchRaw = Math.hypot(landmarks[4].x - landmarks[8].x, landmarks[4].y - landmarks[8].y);

            // Draw visual line connecting thumb & index
            canvasCtx.beginPath();
            canvasCtx.moveTo(landmarks[4].x * canvasElement.width, landmarks[4].y * canvasElement.height);
            canvasCtx.lineTo(landmarks[8].x * canvasElement.width, landmarks[8].y * canvasElement.height);
            canvasCtx.strokeStyle = '#F59E0B';
            canvasCtx.lineWidth = 3;
            canvasCtx.stroke();
        }

        // =====================================================================
        // ANTI-FLOOD DEBOUNCER FOR FINGER COUNT
        // =====================================================================
        if (detectedFingers === candidateFingerCount) {
            consecutiveFrames++;
        } else {
            candidateFingerCount = detectedFingers;
            consecutiveFrames = 1;
        }

        // Lock gesture only after threshold consecutive frames
        if (consecutiveFrames >= CONFIRMATION_THRESHOLD && confirmedFingerCount !== candidateFingerCount) {
            confirmedFingerCount = candidateFingerCount;
            executeFingerAction(confirmedFingerCount);
        }

        // =====================================================================
        // PWM PINCH DIMMER (Continuous Slider with EMA + Throttling)
        // =====================================================================
        if (pwmPin && results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
            // Normalize raw distance (0.04 to 0.22) -> (0 to 255)
            const rawNormalized = Math.min(255, Math.max(0, Math.round(((pinchRaw - 0.04) / 0.18) * 255)));
            
            // Exponential Moving Average (EMA) to eliminate hand tremors
            emaPinch = Math.round(0.3 * rawNormalized + 0.7 * emaPinch);

            const pct = Math.round((emaPinch / 255) * 100);
            dimmerFillBar.style.width = `${pct}%`;
            dimmerValueLabel.innerText = `${pct}% (${emaPinch} / 255)`;

            // Throttle sending to MQTT to avoid flooding
            const timeSinceLastPwm = now - lastPwmSentTime;
            if (timeSinceLastPwm > PWM_THROTTLE_MS && Math.abs(emaPinch - lastPwmSent) >= 8) {
                lastPwmSent = emaPinch;
                lastPwmSentTime = now;
                triggerPinHardware(pwmPin, String(emaPinch), 'Dimmer PWM');
            }
        }

        canvasCtx.restore();
    }

    // Triggered when a finger count is confirmed
    function executeFingerAction(count) {
        heroFingerNum.innerText = count;
        heroGestureTitle.innerText = GESTURE_TITLES[count] || `${count} Fingers`;

        // Pulse effect
        heroFingerNum.classList.add('pulse');
        setTimeout(() => heroFingerNum.classList.remove('pulse'), 300);

        hudActiveGesture.innerHTML = `<i class="ti ti-hand-stop"></i> Gesture: ${count} Fingers`;

        if (count === 0) {
            // Fist = Turn OFF All Digital Channels
            heroActionSummary.innerText = "All Channels OFF";
            heroActionDetail.innerText = "Mutthi band: Sabhi devices ke pins ko 0 bhej diya gaya.";
            if (activeDevice && activeDevice.pins) {
                activeDevice.pins.forEach(pin => {
                    pin.current_state = '0';
                    triggerPinHardware(pin, '0', 'All OFF');
                });
            }
        } else if (pinFingerMap[count]) {
            // Individual mapped pin -> Turn ON
            const targetPin = pinFingerMap[count];
            const appName = targetPin.custom_name || (targetPin.appliance ? targetPin.appliance.name : targetPin.pin_name);
            heroActionSummary.innerText = `${targetPin.pin_name} (${appName}) -> ON`;
            heroActionDetail.innerText = `${count} Finger detected. Hardware signal sent successfully!`;

            targetPin.current_state = '1';
            triggerPinHardware(targetPin, '1', appName);
        } else {
            heroActionSummary.innerText = `No channel mapped for ${count} fingers`;
            heroActionDetail.innerText = `Aap is pin ko settings me assign kar sakte hain.`;
        }
    }

    // =========================================================================
    // 6. CAMERA & MEDIAPIPE INITIALIZATION
    // =========================================================================
    let isMirrored = true;
    document.getElementById('btnToggleMirror').addEventListener('click', () => {
        isMirrored = !isMirrored;
        canvasElement.style.transform = isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
    });

    const hands = new Hands({
        locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`
    });

    hands.setOptions({
        maxNumHands: 1,
        modelComplexity: 0, // Lite Model for 60 FPS
        minDetectionConfidence: 0.6,
        minTrackingConfidence: 0.5
    });

    hands.onResults(onResults);

    let isCamRunning = true;
    const camera = new Camera(videoElement, {
        onFrame: async () => {
            if (isCamRunning) {
                await hands.send({ image: videoElement });
            }
        },
        width: 640,
        height: 480
    });

    camera.start();

    const btnToggleCam = document.getElementById('btnToggleCam');
    btnToggleCam.addEventListener('click', () => {
        isCamRunning = !isCamRunning;
        if (isCamRunning) {
            btnToggleCam.innerHTML = '<i class="ti ti-player-pause"></i> Pause';
            btnToggleCam.className = 'btn btn-sm btn-outline-danger';
        } else {
            btnToggleCam.innerHTML = '<i class="ti ti-player-play"></i> Resume';
            btnToggleCam.className = 'btn btn-sm btn-outline-success';
        }
    });

</script>
@endpush
