@extends('admin.layouts.app')

@section('title', $device->name)

@section('content')

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
                </h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('user.devices.edit', $device->id) }}" class="btn btn-outline-warning">
                    <i class="ti ti-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('user.devices.index') }}" class="btn">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    {{-- Board Info Card --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center g-3">
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">HARDWARE</div>
                    <div class="fw-semibold">NodeMCU ESP8266</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">MAC ADDRESS</div>
                    <div class="fw-bold font-monospace text-uppercase">{{ $device->mac_address }}</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">LOCAL IP</div>
                    <div class="font-monospace">{{ $device->ip_address ?? 'Not assigned' }}</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="text-secondary small">STATUS</div>
                    <div>
                        @if($device->is_online)
                            <span class="badge bg-success-lt"><i class="ti ti-wifi me-1"></i>Online</span>
                        @else
                            <span class="badge bg-secondary-lt"><i class="ti ti-wifi-off me-1"></i>Offline</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Channels / Pins Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="ti ti-circuit-switch me-2 text-primary"></i>Channels & Relays</h3>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1">Pin</th>
                        <th>GPIO</th>
                        <th>Appliance & Name</th>
                        <th>Signal Type</th>
                        <th>State / Control</th>
                        <th class="text-end">Configure</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($device->pins as $pin)
                        <tr>
                            <td>
                                <span class="badge bg-dark text-white font-monospace fs-4 px-2 py-1">
                                    {{ $pin->pin_name }}
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary font-monospace">GPIO {{ $pin->gpio_number }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($pin->appliance)
                                        <i class="{{ $pin->appliance->icon ?: 'ti ti-plug' }} text-primary fs-2 me-2"></i>
                                    @else
                                        <i class="ti ti-power text-secondary fs-2 me-2"></i>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $pin->custom_name ?: ($pin->appliance->name ?? 'Unassigned Channel') }}</div>
                                        <small class="text-secondary">{{ $pin->appliance->name ?? 'Click configure to attach' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($pin->pin_type === 'PWM')
                                    <span class="badge bg-azure-lt">PWM</span>
                                @else
                                    <span class="badge bg-cyan-lt">Digital</span>
                                @endif
                            </td>
                            <td>
                                @if($pin->pin_type === 'PWM')
                                    <div class="d-flex align-items-center gap-2" style="max-width: 150px;">
                                        <span class="badge bg-purple-lt">{{ $pin->current_state }} / 255</span>
                                    </div>
                                @else
                                    <label class="form-check form-switch m-0">
                                        <input class="form-check-input pin-toggle" type="checkbox"
                                            data-device-id="{{ $device->id }}"
                                            data-pin-id="{{ $pin->id }}"
                                            {{ $pin->current_state == '1' ? 'checked' : '' }}>
                                    </label>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-config-pin"
                                    data-pin-id="{{ $pin->id }}"
                                    data-pin-name="{{ $pin->pin_name }}"
                                    data-appliance-id="{{ $pin->appliance_id }}"
                                    data-custom-name="{{ $pin->custom_name }}">
                                    <i class="ti ti-settings me-1"></i>Configure
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Configure Pin Modal --}}
    <div class="modal modal-blur fade" id="config-pin-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="config-pin-form" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Configure Pin <span id="modal-pin-name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="mb-3">
                            <label class="form-label">Attach Appliance</label>
                            <select name="appliance_id" id="modal-appliance-select" class="form-select">
                                <option value="">None (Unassigned)</option>
                                @foreach($appliances as $appliance)
                                    <option value="{{ $appliance->id }}">
                                        {{ $appliance->name }} ({{ $appliance->signal_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Custom Display Name</label>
                            <input type="text" name="custom_name" id="modal-custom-name" class="form-control" placeholder="e.g. Balcony Light, Night Fan">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            // Pin toggle switch
            $(document).on('change', '.pin-toggle', function () {
                let deviceId = $(this).data('device-id');
                let pinId = $(this).data('pin-id');
                let isChecked = $(this).is(':checked');
                let state = isChecked ? '1' : '0';

                $.ajax({
                    url: '/devices/' + deviceId + '/pins/' + pinId + '/toggle',
                    type: 'POST',
                    data: { state: state, _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1200, icon: 'success', title: res.message || 'Updated' });
                    },
                    error: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to toggle switch' });
                    }
                });
            });

            // Open Config Modal
            $('.btn-config-pin').on('click', function () {
                let pinId = $(this).data('pin-id');
                let pinName = $(this).data('pin-name');
                let applianceId = $(this).data('appliance-id');
                let customName = $(this).data('custom-name');

                $('#modal-pin-name').text(pinName);
                $('#modal-appliance-select').val(applianceId);
                $('#modal-custom-name').val(customName);

                $('#config-pin-form').attr('action', '/devices/{{ $device->id }}/pins/' + pinId + '/assign');
                $('#config-pin-modal').modal('show');
            });

        });
    </script>
@endpush
