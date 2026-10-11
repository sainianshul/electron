@extends('admin.layouts.app')

@section('title', 'My Dashboard')

@push('styles')
<style>
    .appliance-box {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
    }
    .appliance-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
    }
    .appliance-box.is-on {
        border-color: rgba(47, 179, 68, 0.4);
    }
    .appliance-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.3s ease;
    }
    .appliance-box.is-on .appliance-icon-wrapper {
        background: rgba(47, 179, 68, 0.15) !important;
        color: #2fb344 !important;
        box-shadow: 0 0 16px rgba(47, 179, 68, 0.3);
    }
    .appliance-box.is-off .appliance-icon-wrapper {
        background: rgba(108, 117, 125, 0.1) !important;
        color: #6c757d !important;
    }
    .power-switch-lg .form-check-input {
        width: 3.2rem;
        height: 1.7rem;
        cursor: pointer;
    }
    .power-switch-lg .form-check-input:checked {
        background-color: #2fb344;
        border-color: #2fb344;
        box-shadow: 0 0 10px rgba(47, 179, 68, 0.4);
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
</style>
@endpush

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Welcome back, {{ auth()->user()->name }}</div>
                <h2 class="page-title">Smart Home Dashboard</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('user.appliances.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-plug me-1"></i>My Appliances
                </a>
                <a href="{{ route('user.devices.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Device
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary-lt text-primary avatar avatar-md">
                                <i class="ti ti-cpu fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2">{{ $stats['total_devices'] }}</div>
                            <div class="text-secondary">My Devices</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-success-lt text-success avatar avatar-md">
                                <i class="ti ti-wifi fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2 text-success">{{ $stats['online_devices'] }}</div>
                            <div class="text-secondary">Online Boards</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-azure-lt text-azure avatar avatar-md">
                                <i class="ti ti-plug fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2">{{ $stats['active_appliances'] }}</div>
                            <div class="text-secondary">Active Appliances</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-green-lt text-green avatar avatar-md">
                                <i class="ti ti-power fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2 text-green">{{ $stats['active_on'] }}</div>
                            <div class="text-secondary">Running (ON) Now</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Appliances Control (Box UI) --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="m-0 fw-bold d-flex align-items-center gap-2">
                <i class="ti ti-switch text-primary"></i>Quick Appliance Controls
            </h3>
            <div class="text-secondary small">Switch on/off your smart lights and fans with instant response.</div>
        </div>
        <a href="{{ route('user.appliances.index') }}" class="btn btn-sm btn-outline-primary">
            Manage All Appliances <i class="ti ti-arrow-right ms-1"></i>
        </a>
    </div>

    @if($assignedPins->isEmpty())
        <div class="card card-dashed text-center py-4 mb-4">
            <div class="card-body">
                <p class="text-secondary m-0">
                    @if($devices->isEmpty())
                        No smart devices found. <a href="{{ route('user.devices.create') }}">Add your first board</a> to connect appliances.
                    @else
                        No appliances attached to your boards yet. <a href="{{ route('user.appliances.index') }}">Go to My Appliances</a> to attach lights or fans.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div class="row row-cards mb-4">
            @foreach($assignedPins->take(6) as $pin)
                @php
                    $isOn = ($pin->pin_type === 'PWM') ? ((int)$pin->current_state > 0) : ($pin->current_state === '1');
                    $iconClass = $pin->appliance ? ($pin->appliance->icon ?: 'ti ti-plug') : 'ti ti-plug';
                @endphp
                <div class="col-sm-6 col-lg-4 col-xl-4" id="appliance-col-{{ $pin->id }}">
                    <div class="card appliance-box {{ $isOn ? 'is-on' : 'is-off' }}" id="card-pin-{{ $pin->id }}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="appliance-icon-wrapper">
                                        @if($pin->appliance && $pin->appliance->image_url)
                                            <img src="{{ $pin->appliance->image_url }}" alt="" style="width: 30px; height: 30px; object-fit: contain;">
                                        @else
                                            <i class="{{ $iconClass }}"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="badge bg-secondary-lt font-monospace text-truncate" style="max-width: 140px;">
                                            <i class="ti ti-cpu me-1"></i>{{ $pin->device->name }}
                                        </div>
                                    </div>
                                </div>
                                <span class="badge {{ $pin->pin_type === 'PWM' ? 'bg-purple-lt' : 'bg-cyan-lt' }}">
                                    {{ $pin->pin_type }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <h4 class="fw-bold mb-0 text-truncate" title="{{ $pin->custom_name }}">
                                    {{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}
                                </h4>
                                <div class="text-secondary small">{{ $pin->appliance->name ?? 'Device Channel' }}</div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                <div class="status-indicator-text" id="status-text-{{ $pin->id }}">
                                    @if($isOn)
                                        <span class="text-success fw-semibold small d-flex align-items-center">
                                            <span class="pulse-dot online"></span>ON
                                        </span>
                                    @else
                                        <span class="text-secondary small d-flex align-items-center">
                                            <span class="pulse-dot offline"></span>OFF
                                        </span>
                                    @endif
                                </div>

                                <div class="power-switch-lg">
                                    <label class="form-check form-switch m-0">
                                        <input class="form-check-input appliance-toggle" type="checkbox"
                                            data-pin-id="{{ $pin->id }}"
                                            data-name="{{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}"
                                            {{ $isOn ? 'checked' : '' }}>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Boards List --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="m-0 fw-bold d-flex align-items-center gap-2">
            <i class="ti ti-cpu text-primary"></i>My Smart Boards
        </h3>
        <a href="{{ route('user.devices.index') }}" class="text-secondary small">View All Devices →</a>
    </div>

    <div class="row row-cards">
        @forelse($devices as $device)
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-sm bg-primary-lt me-2"><i class="ti ti-cpu fs-3"></i></span>
                            <div>
                                <h4 class="card-title m-0">{{ $device->name }}</h4>
                                <small class="text-secondary font-monospace">{{ $device->mac_address }}</small>
                            </div>
                        </div>
                        <div>
                            @if($device->is_online)
                                <span class="badge bg-success-lt"><i class="ti ti-wifi me-1"></i>Online</span>
                            @else
                                <span class="badge bg-secondary-lt"><i class="ti ti-wifi-off me-1"></i>Offline</span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small fw-bold">CHANNELS USAGE</span>
                            <span class="badge bg-azure-lt">{{ $device->pins->where('is_assigned', true)->count() }} / {{ $device->pins->count() }} Used</span>
                        </div>
                        <div class="progress progress-sm">
                            @php
                                $total = $device->pins->count();
                                $used = $device->pins->where('is_assigned', true)->count();
                                $pct = $total > 0 ? round(($used / $total) * 100) : 0;
                            @endphp
                            <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="card-footer bg-light-subtle d-flex justify-content-between align-items-center py-2">
                        <span class="small text-secondary">{{ $device->pins->where('is_assigned', false)->count() }} slots available</span>
                        <a href="{{ route('user.devices.show', $device->id) }}" class="btn btn-sm btn-outline-primary">
                            Manage Board <i class="ti ti-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-dashed text-center py-5">
                    <div class="card-body">
                        <div class="avatar avatar-xl bg-primary-lt mb-3">
                            <i class="ti ti-cpu fs-1"></i>
                        </div>
                        <h3>No Smart Devices Added Yet</h3>
                        <p class="text-secondary mb-4">Add your NodeMCU ESP8266 board to begin controlling your home lights, fans, and appliances.</p>
                        <a href="{{ route('user.devices.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Add My First Device
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

@endsection

@push('scripts')
<script>
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
            $statusText.html('<span class="text-success fw-semibold small d-flex align-items-center"><span class="pulse-dot online"></span>ON</span>');
        } else {
            $card.removeClass('is-on').addClass('is-off');
            $statusText.html('<span class="text-secondary small d-flex align-items-center"><span class="pulse-dot offline"></span>OFF</span>');
        }

        $.ajax({
            url: '/appliances/' + pinId + '/toggle',
            type: 'POST',
            data: { state: state, _token: '{{ csrf_token() }}' },
            success: function (res) {
                showToast(res.message || (applianceName + ' toggled!'), 'success');
            },
            error: function () {
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
</script>
@endpush
