@extends('admin.layouts.app')

@section('title', $device->name)

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
                    ['url' => route('user.devices.index'), 'label' => 'My Devices'],
                    ['label' => $device->name],
                ]" />
                <h2 class="page-title d-flex align-items-center gap-2">
                    <span class="avatar bg-primary-lt">
                        <i class="ti ti-cpu fs-2"></i>
                    </span>
                    {{ $device->name }}
                    @if($device->is_online)
                        <span class="badge bg-success-lt fs-5"><i class="ti ti-wifi me-1"></i>Online</span>
                    @else
                        <span class="badge bg-secondary-lt fs-5"><i class="ti ti-wifi-off me-1"></i>Offline</span>
                    @endif
                </h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
                    <i class="ti ti-plus me-1"></i>Add Appliance
                </button>
                <a href="{{ route('user.devices.edit', $device->id) }}" class="btn btn-outline-warning">
                    <i class="ti ti-pencil me-1"></i>Edit Board
                </a>
                <a href="{{ route('user.devices.index') }}" class="btn">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    {{-- Board Summary Bar --}}
    <div class="card mb-4">
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
                        <span class="badge bg-azure-lt ms-1">{{ $capacity['free_slots'] }} free</span>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">SLOT BREAKDOWN</div>
                    <div class="small">
                        <span class="badge bg-cyan-lt me-1">Digital: {{ $capacity['digital']['free'] }} free</span>
                        <span class="badge bg-purple-lt">PWM: {{ $capacity['pwm']['free'] }} free</span>
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
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
                <i class="ti ti-plus me-1"></i>Add Appliance
            </button>
        </div>
    </div>

    @php
        $assignedPins = $device->pins->where('is_assigned', true);
    @endphp

    @if($assignedPins->isEmpty())
        <div class="card card-dashed text-center py-5 mb-4">
            <div class="card-body">
                <div class="avatar avatar-xl bg-azure-lt mb-3" style="width: 64px; height: 64px;">
                    <i class="ti ti-plug-connected fs-1 text-azure"></i>
                </div>
                <h3 class="fw-bold mb-2">No Appliances Connected to this Board Yet</h3>
                <p class="text-secondary max-w-sm mx-auto mb-4" style="max-width: 480px;">
                    This board has <strong>{{ $capacity['free_slots'] }} available channels</strong> ready. Click below to add a Ceiling Fan, LED Bulb, Smart Switch, or any appliance.
                </p>
                <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addApplianceModal">
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
                            
                            {{-- Card Top: Icon + Signal Badge + Dropdown --}}
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
                                        <span class="badge {{ $pin->pin_type === 'PWM' ? 'bg-purple-lt' : 'bg-cyan-lt' }}">
                                            {{ $pin->pin_type }}
                                        </span>
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

    {{-- Collapsible Hardware Pin Mapping (Advanced / Diagnostics only) --}}
    <div class="card card-stacked mt-4">
        <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#hardware-diagnostics-collapse">
            <h4 class="card-title text-secondary mb-0 d-flex align-items-center justify-content-between w-100">
                <span><i class="ti ti-cpu me-2"></i>Hardware Diagnostics & Board Mapping <small class="text-muted fw-normal">(Advanced)</small></span>
                <i class="ti ti-chevron-down fs-3"></i>
            </h4>
        </div>
        <div class="collapse" id="hardware-diagnostics-collapse">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-sm">
                    <thead>
                        <tr>
                            <th class="w-1">Hardware Pin</th>
                            <th>GPIO</th>
                            <th>Pin Type</th>
                            <th>Attached Appliance</th>
                            <th>Raw State</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($device->pins as $pin)
                            <tr>
                                <td><span class="badge bg-dark-lt font-monospace">{{ $pin->pin_name }}</span></td>
                                <td class="font-monospace text-secondary">GPIO {{ $pin->gpio_number }}</td>
                                <td><span class="badge {{ $pin->pin_type === 'PWM' ? 'bg-purple-lt' : 'bg-cyan-lt' }}">{{ $pin->pin_type }}</span></td>
                                <td>
                                    @if($pin->is_assigned && $pin->appliance)
                                        <span class="fw-semibold text-primary"><i class="ti ti-plug me-1"></i>{{ $pin->custom_name ?: $pin->appliance->name }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Empty / Available</span>
                                    @endif
                                </td>
                                <td><span class="font-monospace">{{ $pin->current_state }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add Appliance to this Device Modal --}}
    <div class="modal modal-blur fade" id="addApplianceModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="add-appliance-form" action="{{ route('user.devices.appliances.attach', $device->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs bg-primary-lt">
                                <i class="ti ti-plus"></i>
                            </span>
                            Add Appliance to {{ $device->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        {{-- Step 1: Select Appliance --}}
                        <div class="mb-3">
                            <label class="form-label required">Select Appliance to Attach</label>
                            <select name="appliance_id" id="modal-select-appliance" class="form-select" required>
                                <option value="" disabled selected>— Choose Appliance —</option>
                                @foreach($appliances as $appliance)
                                    <option value="{{ $appliance->id }}" 
                                        data-name="{{ $appliance->name }}"
                                        data-signal="{{ $appliance->signal_type }}"
                                        data-type="{{ $appliance->type }}">
                                        {{ $appliance->name }} ({{ $appliance->signal_type }} - {{ $appliance->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Step 2: Real-Time Hardware Slot Availability Box --}}
                        <div id="slot-availability-box" class="alert alert-secondary d-none py-2 px-3 mb-3" role="alert">
                            <div class="d-flex align-items-center" id="slot-availability-content">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <span>Checking board channel availability...</span>
                            </div>
                        </div>

                        {{-- Step 3: Custom Display Name --}}
                        <div class="mb-3">
                            <label class="form-label">Custom Display Name (Optional)</label>
                            <input type="text" name="custom_name" id="modal-input-name" class="form-control"
                                placeholder="e.g. Master Bedroom Fan, Balcony Tube Light">
                            <small class="form-hint">A friendly name for this appliance in your home.</small>
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

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        const DEVICE_ID = {{ $device->id }};

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

        // ── 2. Real-time Slot Availability Check in Modal ────────────
        function checkSlotAvailability() {
            let applianceId = $('#modal-select-appliance').val();
            let $box = $('#slot-availability-box');
            let $content = $('#slot-availability-content');
            let $btn = $('#btn-submit-add-appliance');

            if (!applianceId) {
                $box.addClass('d-none');
                $btn.prop('disabled', true);
                return;
            }

            $box.removeClass('d-none alert-success alert-danger alert-secondary')
                .addClass('alert-secondary');
            $content.html('<span class="spinner-border spinner-border-sm me-2"></span> Checking available channels on this board...');
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

        $('#modal-select-appliance').on('change', function () {
            checkSlotAvailability();
            let opt = $(this).find(':selected');
            let name = opt.data('name');
            if (name && !$('#modal-input-name').val()) {
                $('#modal-input-name').attr('placeholder', 'e.g. Master Bedroom ' + name);
            }
        });

        // ── 3. Disconnect / Detach Appliance ────────────────────────
        $(document).on('click', '.btn-detach-appliance', function () {
            let pinId = $(this).data('pin-id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Disconnect ' + name + '?',
                text: 'This will remove the appliance from this device and free up the hardware channel.',
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
