@extends('admin.layouts.app')

@section('title', 'Smart Home Dashboard')

@push('styles')
<style>
    /* Apple Home Style Tokens */
    .apple-stat-card {
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: var(--tblr-card-bg, #ffffff);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .apple-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }
    .apple-icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

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

    /* Apple style big rounded switch */
    .power-switch-lg .form-check-input {
        width: 3.4rem;
        height: 1.85rem;
        cursor: pointer;
        border-radius: 2rem;
        transition: background-color 0.25s ease, box-shadow 0.25s ease;
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
</style>
@endpush

@section('content')

    {{-- Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle text-uppercase fw-bold text-secondary">
                    Welcome back, {{ auth()->user()->name }}
                </div>
                <h2 class="page-title d-flex align-items-center gap-2">
                    <span class="avatar bg-primary-lt" style="border-radius: 12px;">
                        <i class="ti ti-home fs-2 text-primary"></i>
                    </span>
                    Smart Home Dashboard
                </h2>
                <div class="text-secondary small mt-1">
                    Control all your home lights, fans, and appliances in one place.
                </div>
            </div>
            <div class="col-auto">
                <a href="{{ route('user.devices.index') }}" class="btn btn-outline-primary" style="border-radius: 10px;">
                    <i class="ti ti-cpu me-1"></i>My Smart Boards
                </a>
            </div>
        </div>
    </div>

    {{-- Modern Apple Style Stats --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card apple-stat-card card-sm">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="apple-icon-circle bg-primary-lt text-primary">
                                <i class="ti ti-cpu"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-1" id="stat-devices">{{ $stats['total_devices'] }}</div>
                            <div class="text-secondary small">Registered Boards</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card apple-stat-card card-sm">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="apple-icon-circle bg-success-lt text-success">
                                <i class="ti ti-wifi"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-1 text-success" id="stat-online">{{ $stats['online_devices'] }}</div>
                            <div class="text-secondary small">Online Boards</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card apple-stat-card card-sm">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="apple-icon-circle bg-green-lt text-green">
                                <i class="ti ti-power"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-1 text-green" id="stat-on">{{ $stats['active_on'] }}</div>
                            <div class="text-secondary small">Running (ON)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card apple-stat-card card-sm">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="apple-icon-circle bg-secondary-lt text-secondary">
                                <i class="ti ti-moon"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-1 text-secondary" id="stat-off">{{ $stats['active_off'] }}</div>
                            <div class="text-secondary small">Turned OFF</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Section: Smart Home Appliances Grid --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti ti-switch text-primary"></i>Appliances & Switches
        </h3>
    </div>

    @if($assignedPins->isEmpty())
        <div class="card card-dashed text-center py-5 mb-4" style="border-radius: 16px;">
            <div class="card-body">
                <div class="avatar avatar-xl bg-azure-lt mb-3" style="width: 68px; height: 68px; border-radius: 18px;">
                    <i class="ti ti-plug-connected fs-1 text-azure"></i>
                </div>
                @if($devices->isEmpty())
                    <h3 class="fw-bold mb-2">No Smart Boards Connected</h3>
                    <p class="text-secondary max-w-sm mx-auto mb-4" style="max-width: 480px;">
                        To start controlling appliances, you first need to register your smart board (e.g. NodeMCU ESP8266).
                    </p>
                    <a href="{{ route('user.devices.create') }}" class="btn btn-primary" style="border-radius: 10px;">
                        <i class="ti ti-plus me-1"></i>Add My First Board
                    </a>
                @else
                    <h3 class="fw-bold mb-2">No Appliances Added Yet</h3>
                    <p class="text-secondary max-w-sm mx-auto mb-4" style="max-width: 480px;">
                        Your smart board is registered! Go to <strong>My Devices</strong>, open your board, and click <strong>"Add Appliance"</strong> to connect your lights, fans, and switches.
                    </p>
                    <a href="{{ route('user.devices.index') }}" class="btn btn-primary" style="border-radius: 10px;">
                        <i class="ti ti-cpu me-1"></i>Go to My Devices
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="row row-cards mb-4" id="dashboard-appliances-grid">
            @foreach($assignedPins as $pin)
                @php
                    $isOn = ($pin->pin_type === 'PWM') ? ((int)$pin->current_state > 0) : ($pin->current_state === '1');
                    $iconClass = $pin->appliance ? ($pin->appliance->icon ?: 'ti ti-plug') : 'ti ti-plug';
                @endphp
                <div class="col-sm-6 col-lg-4 col-xl-3" id="appliance-col-{{ $pin->id }}">
                    <div class="card appliance-box {{ $isOn ? 'is-on' : 'is-off' }}" id="card-pin-{{ $pin->id }}">
                        <div class="card-body p-3">
                            
                            {{-- Card Top: Icon + Board Pill + Hardware Pin Badge --}}
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
                                        <div class="badge bg-secondary-lt font-monospace text-truncate mb-1" style="max-width: 125px; border-radius: 6px;">
                                            <i class="ti ti-cpu me-1"></i>{{ $pin->device->name }}
                                        </div>
                                        <div>
                                            <span class="badge bg-azure-lt font-monospace fw-bold" style="border-radius: 6px; font-size: 11px;">
                                                <i class="ti ti-circuit-switch me-1"></i>Pin {{ $pin->pin_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge {{ $pin->pin_type === 'PWM' ? 'bg-purple-lt' : 'bg-cyan-lt' }}" style="border-radius: 6px;">
                                    {{ $pin->pin_type }}
                                </span>
                            </div>

                            {{-- Appliance Titles --}}
                            <div class="mb-3">
                                <h4 class="fw-bold mb-0 text-truncate" title="{{ $pin->custom_name }}">
                                    {{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}
                                </h4>
                                <div class="text-secondary small">{{ $pin->appliance->name ?? 'Smart Appliance' }}</div>
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

    {{-- Bottom Section: Smart Boards List --}}
    <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
        <h3 class="m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti ti-cpu text-primary"></i>My Smart Boards
        </h3>
        <a href="{{ route('user.devices.index') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">View All Boards →</a>
    </div>

    <div class="row row-cards">
        @forelse($devices as $device)
            @php
                $totalPins = $device->pins->count();
                $assignedCount = $device->pins->where('is_assigned', true)->count();
                $freeCount = $totalPins - $assignedCount;
                $pct = $totalPins > 0 ? round(($assignedCount / $totalPins) * 100) : 0;
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card apple-stat-card h-100" style="border-radius: 18px; border: 1.5px solid rgba(0, 0, 0, 0.07); overflow: hidden;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            {{-- Board Header: Icon + Name + Status --}}
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="apple-icon-circle {{ $device->is_online ? 'bg-success-lt text-success' : 'bg-primary-lt text-primary' }}" style="width: 46px; height: 46px; border-radius: 14px;">
                                        <i class="ti ti-cpu fs-2"></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold m-0" style="font-size: 16px;">{{ $device->name }}</h4>
                                        <span class="badge bg-secondary-lt font-monospace text-uppercase mt-1" style="border-radius: 6px; font-size: 11px;">
                                            {{ $device->mac_address }}
                                        </span>
                                    </div>
                                </div>

                                @if($device->is_online)
                                    <span class="badge bg-success-lt fw-bold px-2 py-1" style="border-radius: 100px;">
                                        <span class="pulse-dot online" style="margin-right: 4px;"></span>Online
                                    </span>
                                @else
                                    <span class="badge bg-secondary-lt fw-semibold px-2 py-1" style="border-radius: 100px;">
                                        <span class="pulse-dot offline" style="margin-right: 4px;"></span>Offline
                                    </span>
                                @endif
                            </div>

                            {{-- Capacity & Channels Mini Stats --}}
                            <div class="p-3 my-3" style="background: rgba(148, 163, 184, 0.08); border-radius: 14px;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-secondary fw-bold" style="letter-spacing: 0.5px; font-size: 11px;">
                                        CHANNELS CAPACITY
                                    </span>
                                    <span class="fw-bold font-monospace small" style="color: var(--tblr-primary, #206bc4);">
                                        {{ $assignedCount }} / {{ $totalPins }} Used
                                    </span>
                                </div>
                                <div class="progress" style="height: 7px; border-radius: 10px; background: rgba(148, 163, 184, 0.2);">
                                    <div class="progress-bar bg-primary" style="width: {{ $pct }}%; border-radius: 10px;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 small text-secondary">
                                    <span><strong class="text-success">{{ $assignedCount }}</strong> attached</span>
                                    <span><strong class="text-primary">{{ $freeCount }}</strong> available</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer Action --}}
                        <div class="pt-3 d-flex justify-content-between align-items-center border-top">
                            <span class="small text-secondary font-monospace">{{ $device->model ?? 'NodeMCU ESP8266' }}</span>
                            <a href="{{ route('user.devices.show', $device->id) }}" class="btn btn-sm btn-outline-primary fw-bold" style="border-radius: 10px; padding: 6px 14px;">
                                Manage Board <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-dashed text-center py-4" style="border-radius: 16px;">
                    <div class="card-body">
                        <p class="text-secondary m-0">No smart boards found. <a href="{{ route('user.devices.create') }}">Register your first board.</a></p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        // ── Instant ON/OFF Switch Handler ─────────────────────────────
        $(document).on('change', '.appliance-toggle', function () {
            let $switch = $(this);
            let pinId = $switch.data('pin-id');
            let applianceName = $switch.data('name');
            let isChecked = $switch.is(':checked');
            let state = isChecked ? '1' : '0';

            let $card = $('#card-pin-' + pinId);
            let $statusText = $('#status-text-' + pinId);

            // Optimistic visual update
            if (isChecked) {
                $card.removeClass('is-off').addClass('is-on');
                $statusText.html('<span class="text-success fw-semibold small d-flex align-items-center"><span class="pulse-dot online"></span>RUNNING (ON)</span>');
            } else {
                $card.removeClass('is-on').addClass('is-off');
                $statusText.html('<span class="text-secondary small d-flex align-items-center"><span class="pulse-dot offline"></span>OFF</span>');
            }

            $.ajax({
                url: '/appliances/' + pinId + '/toggle',
                type: 'POST',
                data: { state: state, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    showToast(res.message || (applianceName + ' state updated!'), 'success');
                    updateStats();
                },
                error: function () {
                    // Revert switch on error
                    $switch.prop('checked', !isChecked);
                    if (!isChecked) {
                        $card.removeClass('is-off').addClass('is-on');
                    } else {
                        $card.removeClass('is-on').addClass('is-off');
                    }
                    showToast('Failed to toggle ' + applianceName, 'error');
                }
            });
        });

        // ── PWM Slider Real-time Input & Change Handlers ──────────────
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
                url: '/appliances/' + pinId + '/toggle',
                type: 'POST',
                data: { state: val, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    showToast(applianceName + ' level set to ' + Math.round((val / 255) * 100) + '%', 'success');
                    updateStats();
                },
                error: function () {
                    showToast('Failed to update ' + applianceName, 'error');
                }
            });
        });

        // ── PWM Quick Power Toggle Button ──────────────────────────────
        $(document).on('click', '.pwm-quick-toggle', function () {
            let $btn = $(this);
            let pinId = $btn.data('pin-id');
            let applianceName = $btn.data('name');
            let $slider = $('#pwm-slider-' + pinId);
            let currentVal = parseInt($slider.val(), 10);
            let newVal = (currentVal > 0) ? 0 : 255;

            $slider.val(newVal).trigger('input').trigger('change');
        });

        function updateStats() {
            let digitalTotal = $('.appliance-toggle').length;
            let digitalOn = $('.appliance-toggle:checked').length;

            let pwmTotal = $('.pwm-slider').length;
            let pwmOn = $('.pwm-slider').filter(function () {
                return parseInt($(this).val(), 10) > 0;
            }).length;

            let totalOn = digitalOn + pwmOn;
            let totalAll = digitalTotal + pwmTotal;
            let totalOff = totalAll - totalOn;

            $('#stat-on').text(totalOn);
            $('#stat-off').text(totalOff);
        }

    });
</script>
@endpush
