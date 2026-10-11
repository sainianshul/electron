@extends('admin.layouts.app')

@section('title', $device->name)

@push('styles')
<style>
    /* Apple Home Appliance Card */
    .appliance-box {
        border-radius: 18px;
        border: 1.5px solid rgba(0, 0, 0, 0.07);
        background: var(--tblr-card-bg, #ffffff);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.03);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .appliance-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    }
    .appliance-box.is-on {
        border-color: rgba(47, 179, 68, 0.45);
        background: linear-gradient(180deg, rgba(47, 179, 68, 0.04) 0%, var(--tblr-card-bg, #ffffff) 100%);
    }
    .appliance-icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        transition: all 0.3s ease;
    }
    .appliance-box.is-on .appliance-icon-wrapper {
        background: rgba(47, 179, 68, 0.16) !important;
        color: #2fb344 !important;
        box-shadow: 0 0 20px rgba(47, 179, 68, 0.35);
    }
    .appliance-box.is-off .appliance-icon-wrapper {
        background: rgba(108, 117, 125, 0.08) !important;
        color: #8c959f !important;
    }
    .power-switch-lg .form-check-input {
        width: 3.4rem;
        height: 1.85rem;
        cursor: pointer;
        border-radius: 2rem;
    }
    .power-switch-lg .form-check-input:checked {
        background-color: #2fb344;
        border-color: #2fb344;
        box-shadow: 0 0 12px rgba(47, 179, 68, 0.45);
    }
    .pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .pulse-dot.online {
        background-color: #2fb344;
        box-shadow: 0 0 8px #2fb344;
    }
    .pulse-dot.offline {
        background-color: #a8aeb7;
    }

    /* Apple Style PWM Range Slider */
    .pwm-slider-container {
        width: 100%;
        padding: 4px 0;
    }
    .pwm-slider {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(to right, #0284c7 var(--pwm-percent, 0%), rgba(148, 163, 184, 0.25) var(--pwm-percent, 0%));
        outline: none;
        cursor: pointer;
        transition: background 0.1s ease;
    }
    .pwm-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #ffffff;
        border: 2.5px solid #0284c7;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .pwm-slider::-webkit-slider-thumb:hover,
    .pwm-slider:active::-webkit-slider-thumb {
        transform: scale(1.15);
        box-shadow: 0 0 14px rgba(2, 132, 199, 0.55);
    }
    .pwm-slider::-moz-range-thumb {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #ffffff;
        border: 2.5px solid #0284c7;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        cursor: pointer;
    }

    /* Apple UI Appliance Picker Grid */
    .apple-picker-grid {
        max-height: 340px;
        overflow-y: auto;
        padding-right: 4px;
    }
    .apple-picker-card {
        border-radius: 14px;
        border: 1.5px solid rgba(0, 0, 0, 0.08);
        background: var(--tblr-card-bg, #ffffff);
        padding: 12px 6px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        user-select: none;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .apple-picker-card:hover {
        transform: translateY(-2px);
        border-color: #206bc4;
        box-shadow: 0 6px 14px rgba(32, 107, 196, 0.12);
    }
    .apple-picker-card.is-selected {
        border-color: #206bc4 !important;
        background: rgba(32, 107, 196, 0.08) !important;
        box-shadow: 0 0 0 2px rgba(32, 107, 196, 0.25) !important;
    }
    .apple-picker-card .check-pill {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #206bc4;
        color: #ffffff;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }
    .apple-picker-card.is-selected .check-pill {
        display: flex;
    }
    .apple-picker-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        margin: 0 auto 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        background: rgba(32, 107, 196, 0.1);
        color: #206bc4;
    }
    .apple-picker-card.is-selected .apple-picker-icon {
        background: #206bc4;
        color: #ffffff;
    }
</style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['url' => route('user.devices.index'), 'label' => 'My Devices'],
                    ['label' => $device->name],
                ]" />
                <h2 class="page-title d-flex align-items-center gap-2">
                    <span class="avatar bg-primary-lt" style="border-radius: 12px;">
                        <i class="ti ti-cpu fs-2 text-primary"></i>
                    </span>
                    {{ $device->name }}
                    @if($device->is_online)
                        <span class="badge bg-success-lt fs-5" style="border-radius: 6px;"><i class="ti ti-wifi me-1"></i>Online</span>
                    @else
                        <span class="badge bg-secondary-lt fs-5" style="border-radius: 6px;"><i class="ti ti-wifi-off me-1"></i>Offline</span>
                    @endif
                </h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-primary" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
                    <i class="ti ti-plus me-1"></i>Add Appliance
                </button>
                <a href="{{ route('user.devices.edit', $device->id) }}" class="btn btn-outline-warning" style="border-radius: 10px;">
                    <i class="ti ti-pencil me-1"></i>Edit Board
                </a>
                <a href="{{ route('user.devices.index') }}" class="btn" style="border-radius: 10px;">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    {{-- Board Summary Bar --}}
    <div class="card mb-4" style="border-radius: 16px;">
        <div class="card-body py-3">
            <div class="row align-items-center g-3">
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">MAC ADDRESS</div>
                    <div class="fw-bold font-monospace text-uppercase">{{ $device->mac_address }}</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">LOCAL IP</div>
                    <div class="font-monospace">{{ $device->ip_address ?? 'Not assigned' }}</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">CHANNEL CAPACITY</div>
                    <div class="fw-semibold">
                        {{ $capacity['assigned_slots'] }} of {{ $capacity['total_slots'] }} slots used
                        <span class="badge bg-azure-lt ms-1" style="border-radius: 6px;">{{ $capacity['free_slots'] }} free</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">SLOT BREAKDOWN</div>
                    <div class="small">
                        <span class="badge bg-cyan-lt me-1" style="border-radius: 6px;">Digital: {{ $capacity['digital']['free'] }} free</span>
                        <span class="badge bg-purple-lt" style="border-radius: 6px;">PWM: {{ $capacity['pwm']['free'] }} free</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section: Connected Appliances (Box UI) --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="m-0 fw-bold d-flex align-items-center gap-2">
                <i class="ti ti-plug text-primary"></i>Connected Appliances
            </h3>
            <div class="text-secondary small">Control and toggle your attached lights, fans, and smart appliances.</div>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-primary" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
                <i class="ti ti-plus me-1"></i>Add Appliance
            </button>
        </div>
    </div>

    @php
        $assignedPins = $device->pins->where('is_assigned', true);
    @endphp

    @if($assignedPins->isEmpty())
        <div class="card card-dashed text-center py-5 mb-4" style="border-radius: 16px;">
            <div class="card-body">
                <div class="avatar avatar-xl bg-azure-lt mb-3" style="width: 64px; height: 64px; border-radius: 16px;">
                    <i class="ti ti-plug-connected fs-1 text-azure"></i>
                </div>
                <h3 class="fw-bold mb-2">No Appliances Connected to this Board Yet</h3>
                <p class="text-secondary max-w-sm mx-auto mb-4" style="max-width: 480px;">
                    This board has <strong>{{ $capacity['free_slots'] }} available channels</strong> ready. Click below to add a Ceiling Fan, LED Bulb, Smart Switch, or any appliance.
                </p>
                <button type="button" class="btn btn-primary btn-lg" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
                    <i class="ti ti-plus me-1"></i>Add Appliance to this Device
                </button>
            </div>
        </div>
    @else
        <div class="row row-cards mb-4" id="device-appliances-grid">
            @foreach($assignedPins as $pin)
                @php
                    $isOn = ($pin->pin_type === 'PWM') ? ((int)$pin->current_state > 0) : ($pin->current_state === '1');
                    $iconClass = $pin->appliance ? ($pin->appliance->icon ?: 'ti ti-plug') : 'ti ti-plug';
                @endphp
                <div class="col-sm-6 col-lg-4 col-xl-3" id="appliance-col-{{ $pin->id }}">
                    <div class="card appliance-box {{ $isOn ? 'is-on' : 'is-off' }}" id="card-pin-{{ $pin->id }}">
                        <div class="card-body p-3">
                            
                            {{-- Card Top: Icon + Pin Badge + Signal Badge + Dropdown --}}
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="appliance-icon-wrapper" id="icon-wrapper-{{ $pin->id }}">
                                        @if($pin->appliance && $pin->appliance->image_url)
                                            <img src="{{ $pin->appliance->image_url }}" alt="{{ $pin->appliance->name }}" style="width: 36px; height: 36px; object-fit: contain;">
                                        @else
                                            <i class="{{ $iconClass }}"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="badge bg-azure-lt font-monospace fw-bold mb-1 d-inline-block" style="border-radius: 6px; font-size: 11px;">
                                            <i class="ti ti-circuit-switch me-1"></i>Pin {{ $pin->pin_name }}
                                        </span>
                                        <div>
                                            <span class="badge {{ $pin->pin_type === 'PWM' ? 'bg-purple-lt' : 'bg-cyan-lt' }}" style="border-radius: 6px;">
                                                {{ $pin->pin_type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Disconnect Button --}}
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-sm btn-ghost-secondary" data-bs-toggle="dropdown" aria-label="Options">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <button type="button" class="dropdown-item text-danger btn-detach-appliance"
                                            data-pin-id="{{ $pin->id }}"
                                            data-name="{{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}">
                                            <i class="ti ti-trash me-2"></i>Disconnect Appliance
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Appliance Titles --}}
                            <div class="mb-3">
                                <h4 class="fw-bold mb-0 text-truncate" title="{{ $pin->custom_name }}">
                                    {{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}
                                </h4>
                                <div class="text-secondary small">{{ $pin->appliance->name ?? 'Device Channel' }}</div>
                            </div>

                            @if($pin->pin_type === 'PWM')
                                @php
                                    $pwmVal = (int)$pin->current_state;
                                    $pwmPct = round(($pwmVal / 255) * 100);
                                @endphp
                                {{-- PWM Slider Control Bar --}}
                                <div class="pt-2 border-top">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="status-indicator-text" id="status-text-{{ $pin->id }}">
                                            @if($isOn)
                                                <span class="text-success fw-semibold small d-flex align-items-center">
                                                    <span class="pulse-dot online"></span>RUNNING (<span id="pwm-pct-text-{{ $pin->id }}">{{ $pwmPct }}%</span>)
                                                </span>
                                            @else
                                                <span class="text-secondary small d-flex align-items-center">
                                                    <span class="pulse-dot offline"></span>OFF (0%)
                                                </span>
                                            @endif
                                        </div>
                                        <span class="badge bg-purple-lt font-monospace fw-bold" id="pwm-badge-{{ $pin->id }}">
                                            {{ $pwmPct }}%
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-icon btn-sm {{ $isOn ? 'btn-success' : 'btn-ghost-secondary' }} pwm-quick-toggle"
                                            data-pin-id="{{ $pin->id }}"
                                            data-name="{{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}"
                                            title="{{ $isOn ? 'Turn OFF' : 'Turn ON' }}"
                                            style="width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;"
                                            id="pwm-power-btn-{{ $pin->id }}">
                                            <i class="ti ti-power"></i>
                                        </button>
                                        <div class="pwm-slider-container flex-grow-1">
                                            <input type="range" class="pwm-slider" min="0" max="255" step="5"
                                                value="{{ $pwmVal }}"
                                                data-pin-id="{{ $pin->id }}"
                                                data-name="{{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}"
                                                id="pwm-slider-{{ $pin->id }}"
                                                style="--pwm-percent: {{ $pwmPct }}%;">
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Live Status Indicator & Digital Switch Bar --}}
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                    <div>
                                        <div class="status-indicator-text" id="status-text-{{ $pin->id }}">
                                            @if($isOn)
                                                <span class="text-success fw-semibold small d-flex align-items-center">
                                                    <span class="pulse-dot online"></span>RUNNING (ON)
                                                </span>
                                            @else
                                                <span class="text-secondary small d-flex align-items-center">
                                                    <span class="pulse-dot offline"></span>OFF
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- The ON/OFF Switch --}}
                                    <div class="power-switch-lg">
                                        <label class="form-check form-switch m-0">
                                            <input class="form-check-input appliance-toggle" type="checkbox"
                                                data-pin-id="{{ $pin->id }}"
                                                data-name="{{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}"
                                                {{ $isOn ? 'checked' : '' }}>
                                        </label>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Apple UI Style Add Appliance Modal --}}
    <div class="modal modal-blur fade" id="addApplianceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                <form id="add-appliance-form" action="{{ route('user.devices.appliances.attach', $device->id) }}" method="POST">
                    @csrf
                    
                    {{-- Hidden Selected Appliance ID --}}
                    <input type="hidden" name="appliance_id" id="selected-appliance-id" value="" required>

                    <div class="modal-header py-3 px-4 border-bottom">
                        <div>
                            <h4 class="modal-title fw-bold m-0 d-flex align-items-center gap-2">
                                <span class="avatar avatar-xs bg-primary-lt" style="border-radius: 8px;">
                                    <i class="ti ti-plus text-primary"></i>
                                </span>
                                Add Appliance to {{ $device->name }}
                            </h4>
                            <div class="text-secondary small">Select an appliance from the catalog below to pair with this board.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">

                        {{-- Step 1: Apple UI Card Grid Picker --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary text-uppercase small mb-2">Choose Appliance</label>
                            
                            <div class="apple-picker-grid">
                                <div class="row g-2">
                                    @foreach($appliances as $appliance)
                                        @php
                                            $icon = $appliance->icon ?: 'ti ti-plug';
                                        @endphp
                                        <div class="col-4 col-sm-4 col-md-3">
                                            <div class="apple-picker-card" 
                                                data-id="{{ $appliance->id }}"
                                                data-name="{{ $appliance->name }}"
                                                data-signal="{{ $appliance->signal_type }}">
                                                
                                                <div class="check-pill">
                                                    <i class="ti ti-check"></i>
                                                </div>

                                                <div class="apple-picker-icon">
                                                    @if($appliance->image_url)
                                                        <img src="{{ $appliance->image_url }}" alt="{{ $appliance->name }}" style="width: 32px; height: 32px; object-fit: contain;">
                                                    @else
                                                        <i class="{{ $icon }}"></i>
                                                    @endif
                                                </div>

                                                <div class="fw-semibold small text-truncate w-100" title="{{ $appliance->name }}">
                                                    {{ $appliance->name }}
                                                </div>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    {{ $appliance->signal_type === 'PWM' ? 'Dimmer / Speed' : 'Relay Switch' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Slot Availability Feedback Banner --}}
                        <div id="slot-availability-box" class="alert alert-secondary d-none py-2 px-3 mb-3" style="border-radius: 12px;" role="alert">
                            <div class="d-flex align-items-center" id="slot-availability-content">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <span>Checking board compatibility...</span>
                            </div>
                        </div>

                        {{-- Step 3: Custom Display Name --}}
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Custom Display Name (Optional)</label>
                            <input type="text" name="custom_name" id="modal-input-name" class="form-control" style="border-radius: 10px;"
                                placeholder="e.g. Master Bedroom Fan, Balcony Light">
                            <small class="form-hint">Give this appliance a recognizable name for quick control.</small>
                        </div>

                    </div>

                    <div class="modal-footer px-4 py-3 bg-light-subtle">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="btn-submit-add-appliance" style="border-radius: 10px;" disabled>
                            <i class="ti ti-plug me-1"></i>Connect Appliance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        const DEVICE_ID = {{ $device->id }};

        // ── 1. Apple UI Card Picker Selection ─────────────────────────
        $(document).on('click', '.apple-picker-card', function () {
            $('.apple-picker-card').removeClass('is-selected');
            $(this).addClass('is-selected');

            let applianceId = $(this).data('id');
            let applianceName = $(this).data('name');

            $('#selected-appliance-id').val(applianceId);

            if (!$('#modal-input-name').val()) {
                $('#modal-input-name').attr('placeholder', 'e.g. Living Room ' + applianceName);
            }

            checkSlotAvailability(applianceId);
        });

        // ── 2. Check Slot Availability on Selection ───────────────────
        function checkSlotAvailability(applianceId) {
            let $box = $('#slot-availability-box');
            let $content = $('#slot-availability-content');
            let $btn = $('#btn-submit-add-appliance');

            $box.removeClass('d-none alert-success alert-danger alert-secondary')
                .addClass('alert-secondary');
            $content.html('<span class="spinner-border spinner-border-sm me-2"></span> Checking board channel availability...');
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("user.appliances.check-availability") }}',
                type: 'GET',
                data: {
                    device_id: DEVICE_ID,
                    appliance_id: applianceId
                },
                success: function (res) {
                    if (res.available) {
                        $box.removeClass('alert-secondary alert-danger').addClass('alert-success');
                        $content.html('<i class="ti ti-circle-check text-success fs-2 me-2"></i><div><strong>Ready to Connect!</strong> ' + res.message + '</div>');
                        $btn.prop('disabled', false);
                    } else {
                        $box.removeClass('alert-secondary alert-success').addClass('alert-danger');
                        $content.html('<i class="ti ti-alert-triangle text-danger fs-2 me-2"></i><div><strong>Channels Full:</strong> ' + res.message + '</div>');
                        $btn.prop('disabled', true);
                    }
                },
                error: function (xhr) {
                    $box.removeClass('alert-secondary alert-success').addClass('alert-danger');
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error checking channel.';
                    $content.html('<i class="ti ti-alert-circle text-danger fs-2 me-2"></i><div>' + msg + '</div>');
                    $btn.prop('disabled', true);
                }
            });
        }

        // ── 3. Toggle Switch Handler ─────────────────────────────────
        $(document).on('change', '.appliance-toggle', function () {
            let $switch = $(this);
            let pinId = $switch.data('pin-id');
            let applianceName = $switch.data('name');
            let isChecked = $switch.is(':checked');
            let state = isChecked ? '1' : '0';

            let $card = $('#card-pin-' + pinId);
            let $statusText = $('#status-text-' + pinId);

            if (isChecked) {
                $card.removeClass('is-off').addClass('is-on');
                $statusText.html('<span class="text-success fw-semibold small d-flex align-items-center"><span class="pulse-dot online"></span>RUNNING (ON)</span>');
            } else {
                $card.removeClass('is-on').addClass('is-off');
                $statusText.html('<span class="text-secondary small d-flex align-items-center"><span class="pulse-dot offline"></span>OFF</span>');
            }

            $.ajax({
                url: '/devices/' + DEVICE_ID + '/pins/' + pinId + '/toggle',
                type: 'POST',
                data: {
                    state: state,
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    showToast(res.message || (applianceName + ' state updated!'), 'success');
                },
                error: function () {
                    $switch.prop('checked', !isChecked);
                    if (!isChecked) {
                        $card.removeClass('is-off').addClass('is-on');
                    } else {
                        $card.removeClass('on').addClass('is-off');
                    }
                    showToast('Failed to toggle ' + applianceName, 'error');
                }
            });
        });

        // ── 3.1 PWM Slider Real-time Input & Change Handlers ─────────
        $(document).on('input', '.pwm-slider', function () {
            let $slider = $(this);
            let pinId = $slider.data('pin-id');
            let val = parseInt($slider.val(), 10);
            let pct = Math.round((val / 255) * 100);

            $slider.css('--pwm-percent', pct + '%');
            $('#pwm-badge-' + pinId).text(pct + '%');
            $('#pwm-pct-text-' + pinId).text(pct + '%');

            let $card = $('#card-pin-' + pinId);
            let $powerBtn = $('#pwm-power-btn-' + pinId);
            let $statusText = $('#status-text-' + pinId);

            if (val > 0) {
                $card.removeClass('is-off').addClass('is-on');
                $powerBtn.removeClass('btn-ghost-secondary').addClass('btn-success');
                $statusText.html('<span class="text-success fw-semibold small d-flex align-items-center"><span class="pulse-dot online"></span>RUNNING (<span id="pwm-pct-text-' + pinId + '">' + pct + '%</span>)</span>');
            } else {
                $card.removeClass('is-on').addClass('is-off');
                $powerBtn.removeClass('btn-success').addClass('btn-ghost-secondary');
                $statusText.html('<span class="text-secondary small d-flex align-items-center"><span class="pulse-dot offline"></span>OFF (0%)</span>');
            }
        });

        $(document).on('change', '.pwm-slider', function () {
            let $slider = $(this);
            let pinId = $slider.data('pin-id');
            let applianceName = $slider.data('name');
            let val = $slider.val();

            $.ajax({
                url: '/devices/' + DEVICE_ID + '/pins/' + pinId + '/toggle',
                type: 'POST',
                data: {
                    state: val,
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    showToast(applianceName + ' level set to ' + Math.round((val / 255) * 100) + '%', 'success');
                },
                error: function () {
                    showToast('Failed to update ' + applianceName, 'error');
                }
            });
        });

        // ── 3.2 PWM Quick Power Toggle Button ─────────────────────────
        $(document).on('click', '.pwm-quick-toggle', function () {
            let $btn = $(this);
            let pinId = $btn.data('pin-id');
            let $slider = $('#pwm-slider-' + pinId);
            let currentVal = parseInt($slider.val(), 10);
            let newVal = (currentVal > 0) ? 0 : 255;

            $slider.val(newVal).trigger('input').trigger('change');
        });

        // ── 4. Disconnect Appliance ──────────────────────────────────
        $(document).on('click', '.btn-detach-appliance', function () {
            let pinId = $(this).data('pin-id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Disconnect ' + name + '?',
                text: 'This will remove the appliance from this board and free up the channel.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Disconnect',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-light ms-2'
                },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: '/devices/' + DEVICE_ID + '/pins/' + pinId + '/detach',
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        showToast(name + ' disconnected successfully', 'success');
                        setTimeout(function () {
                            location.reload();
                        }, 500);
                    },
                    error: function () {
                        showToast('Failed to disconnect appliance.', 'error');
                    }
                });
            });
        });

    });
</script>
@endpush
