@extends('admin.layouts.app')

@section('title', 'My Appliances')

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
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        transition: all 0.3s ease;
    }
    .appliance-box.is-on .appliance-icon-wrapper {
        background: rgba(47, 179, 68, 0.15) !important;
        color: #2fb344 !important;
        box-shadow: 0 0 18px rgba(47, 179, 68, 0.3);
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

    {{-- Page Header --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'My Appliances'],
                ]" />
                <h2 class="page-title d-flex align-items-center gap-2">
                    <span class="avatar bg-primary-lt">
                        <i class="ti ti-plug fs-2"></i>
                    </span>
                    My Smart Appliances
                </h2>
                <div class="text-secondary mt-1">Control your lights, fans, and appliances directly from here.</div>
            </div>
            <div class="col-auto">
                @if($hasDevices)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
                        <i class="ti ti-plus me-1"></i>Add Appliance
                    </button>
                @else
                    <a href="{{ route('user.devices.create') }}" class="btn btn-primary">
                        <i class="ti ti-cpu me-1"></i>Add Device First
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Case 1: User has NO devices registered yet --}}
    @if(!$hasDevices)
        <div class="card card-dashed text-center py-5 my-3">
            <div class="card-body">
                <div class="avatar avatar-xl bg-primary-lt mb-3" style="width: 72px; height: 72px;">
                    <i class="ti ti-router fs-1 text-primary"></i>
                </div>
                <h3 class="fw-bold mb-2">No Smart Device Connected</h3>
                <p class="text-secondary max-w-sm mx-auto mb-4" style="max-width: 480px;">
                    Before you can add and control home appliances (fans, bulbs, switches), you need to register your smart board (e.g. NodeMCU ESP8266).
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('user.devices.create') }}" class="btn btn-primary btn-lg">
                        <i class="ti ti-plus me-2"></i>Add Smart Device
                    </a>
                </div>
            </div>
        </div>

    {{-- Case 2: User HAS devices --}}
    @else

        {{-- Quick Stats Bar --}}
        <div class="row row-deck row-cards mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary-lt text-primary avatar avatar-md">
                                    <i class="ti ti-plug fs-2"></i>
                                </span>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-2" id="stat-total">{{ $stats['total'] }}</div>
                                <div class="text-secondary">Connected Appliances</div>
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
                                    <i class="ti ti-power fs-2"></i>
                                </span>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-2 text-success" id="stat-on">{{ $stats['on'] }}</div>
                                <div class="text-secondary">Currently Running (ON)</div>
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
                                <span class="bg-secondary-lt text-secondary avatar avatar-md">
                                    <i class="ti ti-circle-half fs-2"></i>
                                </span>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-2 text-secondary" id="stat-off">{{ $stats['off'] }}</div>
                                <div class="text-secondary">Turned OFF</div>
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
                                <span class="bg-purple-lt text-purple avatar avatar-md">
                                    <i class="ti ti-cpu fs-2"></i>
                                </span>
                            </div>
                            <div class="col">
                                <div class="fw-bold fs-2">{{ $stats['devices_count'] }}</div>
                                <div class="text-secondary">Registered Boards</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Appliances Box UI Grid --}}
        @if($assignedPins->isEmpty())
            <div class="card card-dashed text-center py-5 my-3">
                <div class="card-body">
                    <div class="avatar avatar-xl bg-azure-lt mb-3" style="width: 64px; height: 64px;">
                        <i class="ti ti-plug-connected fs-1 text-azure"></i>
                    </div>
                    <h3 class="fw-bold mb-2">No Appliances Added to Devices Yet</h3>
                    <p class="text-secondary max-w-sm mx-auto mb-4" style="max-width: 480px;">
                        Your smart board is registered. Now click <strong>"Add Appliance"</strong> to connect your fans, lights, or switches to an available channel.
                    </p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
                        <i class="ti ti-plus me-1"></i>Add My First Appliance
                    </button>
                </div>
            </div>
        @else
            <div class="row row-cards" id="appliances-grid">
                @foreach($assignedPins as $pin)
                    @php
                        $isOn = ($pin->pin_type === 'PWM') ? ((int)$pin->current_state > 0) : ($pin->current_state === '1');
                        $iconClass = $pin->appliance->icon ?: 'ti ti-plug';
                    @endphp
                    <div class="col-sm-6 col-lg-4 col-xl-3" id="appliance-col-{{ $pin->id }}">
                        <div class="card appliance-box {{ $isOn ? 'is-on' : 'is-off' }}" id="card-pin-{{ $pin->id }}">
                            <div class="card-body p-3">
                                
                                {{-- Card Top Header: Icon + Device Badge + Dropdown Menu --}}
                                <div class="d-flex align-items-start justify-content-between mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="appliance-icon-wrapper" id="icon-wrapper-{{ $pin->id }}">
                                            @if($pin->appliance && $pin->appliance->image_url)
                                                <img src="{{ $pin->appliance->image_url }}" alt="{{ $pin->appliance->name }}" style="width: 34px; height: 34px; object-fit: contain;">
                                            @else
                                                <i class="{{ $iconClass }}"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="badge bg-secondary-lt font-monospace text-truncate" style="max-width: 120px;" title="{{ $pin->device->name }}">
                                                <i class="ti ti-cpu me-1"></i>{{ $pin->device->name }}
                                            </div>
                                            <div>
                                                <span class="badge {{ $pin->pin_type === 'PWM' ? 'bg-purple-lt' : 'bg-cyan-lt' }} mt-1">
                                                    {{ $pin->pin_type }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Actions Dropdown --}}
                                    <div class="dropdown">
                                        <button class="btn btn-icon btn-sm btn-ghost-secondary" data-bs-toggle="dropdown" aria-label="Appliance options">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="{{ route('user.devices.show', $pin->device_id) }}" class="dropdown-item">
                                                <i class="ti ti-settings me-2"></i>Board Details
                                            </a>
                                            <button type="button" class="dropdown-item text-danger btn-detach-appliance"
                                                data-pin-id="{{ $pin->id }}"
                                                data-name="{{ $pin->custom_name ?: $pin->appliance->name }}">
                                                <i class="ti ti-trash me-2"></i>Disconnect Appliance
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Appliance Title & Subtitle --}}
                                <div class="mb-3">
                                    <h4 class="fw-bold mb-0 text-truncate" title="{{ $pin->custom_name }}">
                                        {{ $pin->custom_name ?: ($pin->appliance->name ?? 'Appliance') }}
                                    </h4>
                                    <div class="text-secondary small">{{ $pin->appliance->name ?? 'Device Channel' }}</div>
                                </div>

                                {{-- Live Status Indicator & Switch Bar --}}
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

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Add Appliance Modal --}}
        <div class="modal modal-blur fade" id="addApplianceModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form id="add-appliance-form" action="{{ route('user.appliances.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title d-flex align-items-center gap-2">
                                <span class="avatar avatar-xs bg-primary-lt">
                                    <i class="ti ti-plus"></i>
                                </span>
                                Add New Appliance
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            
                            {{-- Step 1: Select Target Device --}}
                            <div class="mb-3">
                                <label class="form-label required">Select Smart Board / Device</label>
                                <select name="device_id" id="modal-select-device" class="form-select" required>
                                    @foreach($devices as $device)
                                        @php
                                            $freePinsCount = $device->pins->where('is_assigned', false)->count();
                                        @endphp
                                        <option value="{{ $device->id }}" data-free-pins="{{ $freePinsCount }}">
                                            {{ $device->name }} ({{ $freePinsCount }} slots free)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Step 2: Select Appliance from Catalog --}}
                            <div class="mb-3">
                                <label class="form-label required">Select Appliance to Attach</label>
                                <select name="appliance_id" id="modal-select-appliance" class="form-select" required>
                                    <option value="" disabled selected>— Choose Appliance —</option>
                                    @foreach($catalogAppliances as $appliance)
                                        <option value="{{ $appliance->id }}" 
                                            data-name="{{ $appliance->name }}"
                                            data-signal="{{ $appliance->signal_type }}"
                                            data-type="{{ $appliance->type }}"
                                            data-icon="{{ $appliance->icon }}">
                                            {{ $appliance->name }} ({{ $appliance->signal_type }} - {{ $appliance->type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Step 3: Real-Time Hardware Slot Availability Box --}}
                            <div id="slot-availability-box" class="alert alert-secondary d-none py-2 px-3 mb-3" role="alert">
                                <div class="d-flex align-items-center" id="slot-availability-content">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    <span>Checking board channel availability...</span>
                                </div>
                            </div>

                            {{-- Step 4: Custom Display Name --}}
                            <div class="mb-3">
                                <label class="form-label">Custom Name (Optional)</label>
                                <input type="text" name="custom_name" id="modal-input-name" class="form-control"
                                    placeholder="e.g. Master Bedroom Fan, Balcony Bulb">
                                <small class="form-hint">Give this appliance a recognizable room or spot name.</small>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="btn-submit-add-appliance" disabled>
                                <i class="ti ti-check me-1"></i>Add Appliance
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        // ── 1. ON/OFF Toggle Switch Handler ──────────────────────────
        $(document).on('change', '.appliance-toggle', function () {
            let $switch = $(this);
            let pinId = $switch.data('pin-id');
            let applianceName = $switch.data('name');
            let isChecked = $switch.is(':checked');
            let state = isChecked ? '1' : '0';

            let $card = $('#card-pin-' + pinId);
            let $statusText = $('#status-text-' + pinId);

            // Optimistic UI updates
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
                data: {
                    state: state,
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    showToast(res.message || (applianceName + ' state updated!'), 'success');
                    updateStats();
                },
                error: function (xhr) {
                    // Revert switch on failure
                    $switch.prop('checked', !isChecked);
                    if (!isChecked) {
                        $card.removeClass('is-off').addClass('is-on');
                    } else {
                        $card.removeClass('is-on').addClass('is-off');
                    }
                    showToast('Failed to toggle ' + applianceName + '. Check device connectivity.', 'error');
                }
            });
        });

        // ── 2. Update Stats Counter ─────────────────────────────────
        function updateStats() {
            let total = $('.appliance-toggle').length;
            let on = $('.appliance-toggle:checked').length;
            let off = total - on;
            $('#stat-total').text(total);
            $('#stat-on').text(on);
            $('#stat-off').text(off);
        }

        // ── 3. Real-time Slot Availability Check in Modal ────────────
        function checkSlotAvailability() {
            let deviceId = $('#modal-select-device').val();
            let applianceId = $('#modal-select-appliance').val();
            let $box = $('#slot-availability-box');
            let $content = $('#slot-availability-content');
            let $btn = $('#btn-submit-add-appliance');

            if (!deviceId || !applianceId) {
                $box.addClass('d-none');
                $btn.prop('disabled', true);
                return;
            }

            $box.removeClass('d-none alert-success alert-danger alert-secondary')
                .addClass('alert-secondary');
            $content.html('<span class="spinner-border spinner-border-sm me-2"></span> Checking available channels...');
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("user.appliances.check-availability") }}',
                type: 'GET',
                data: {
                    device_id: deviceId,
                    appliance_id: applianceId
                },
                success: function (res) {
                    if (res.available) {
                        $box.removeClass('alert-secondary alert-danger').addClass('alert-success');
                        $content.html('<i class="ti ti-circle-check text-success fs-2 me-2"></i><div><strong>Slot Available!</strong> ' + res.message + '</div>');
                        $btn.prop('disabled', false);
                    } else {
                        $box.removeClass('alert-secondary alert-success').addClass('alert-danger');
                        $content.html('<i class="ti ti-alert-triangle text-danger fs-2 me-2"></i><div><strong>Slot Unavailable:</strong> ' + res.message + '</div>');
                        $btn.prop('disabled', true);
                    }
                },
                error: function (xhr) {
                    $box.removeClass('alert-secondary alert-success').addClass('alert-danger');
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error checking slot compatibility.';
                    $content.html('<i class="ti ti-alert-circle text-danger fs-2 me-2"></i><div>' + msg + '</div>');
                    $btn.prop('disabled', true);
                }
            });
        }

        $('#modal-select-device, #modal-select-appliance').on('change', checkSlotAvailability);

        // Pre-fill custom name placeholder on appliance change
        $('#modal-select-appliance').on('change', function () {
            let opt = $(this).find(':selected');
            let name = opt.data('name');
            if (name && !$('#modal-input-name').val()) {
                $('#modal-input-name').attr('placeholder', 'e.g. Master Bedroom ' + name);
            }
        });

        // ── 4. Disconnect / Detach Appliance ────────────────────────
        $(document).on('click', '.btn-detach-appliance', function () {
            let pinId = $(this).data('pin-id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Disconnect ' + name + '?',
                text: 'This will remove the appliance from this device and free up the channel.',
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
                    url: '/appliances/' + pinId,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        showToast(res.message || 'Appliance disconnected successfully', 'success');
                        $('#appliance-col-' + pinId).fadeOut(300, function () {
                            $(this).remove();
                            updateStats();
                            if ($('#appliances-grid .appliance-box').length === 0) {
                                location.reload();
                            }
                        });
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
