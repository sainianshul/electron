@extends('admin.layouts.app')

@section('title', 'My Dashboard')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">Welcome back, {{ auth()->user()->name }}</div>
                <h2 class="page-title">Smart Home Dashboard</h2>
            </div>
            <div class="col-auto">
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
                            <div class="fw-bold fs-2">{{ $stats['online_devices'] }}</div>
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
                            <span class="bg-secondary-lt text-secondary avatar avatar-md">
                                <i class="ti ti-wifi-off fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2">{{ $stats['offline_devices'] }}</div>
                            <div class="text-secondary">Offline Boards</div>
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
                                <i class="ti ti-power fs-2"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-2">{{ $stats['active_switches'] }}</div>
                            <div class="text-secondary">Active Appliances</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Devices Section --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="m-0 fw-bold">My Smart Boards</h3>
        <a href="{{ route('user.devices.index') }}" class="text-secondary small">View All Devices →</a>
    </div>

    <div class="row row-cards">
        @forelse($devices as $device)
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
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
                        <div class="text-secondary small fw-bold mb-2">APPLIANCES & SWITCHES</div>
                        <div class="list-group list-group-flush border rounded-2">
                            @foreach($device->pins as $pin)
                                <div class="list-group-item d-flex justify-content-between align-items-center p-2">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-dark-lt font-monospace me-2">{{ $pin->pin_name }}</span>
                                        <div>
                                            <div class="fw-semibold text-truncate" style="max-width: 140px;">
                                                {{ $pin->custom_name ?: ($pin->appliance->name ?? 'Unassigned') }}
                                            </div>
                                            <small class="text-secondary">{{ $pin->pin_type }}</small>
                                        </div>
                                    </div>
                                    <div>
                                        @if($pin->pin_type === 'PWM')
                                            <span class="badge bg-azure-lt">{{ $pin->current_state }} / 255</span>
                                        @else
                                            <label class="form-check form-switch m-0">
                                                <input class="form-check-input pin-toggle-switch" type="checkbox" 
                                                    data-device-id="{{ $device->id }}"
                                                    data-pin-id="{{ $pin->id }}"
                                                    {{ $pin->current_state == '1' ? 'checked' : '' }}>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer bg-light-subtle text-end py-2">
                        <a href="{{ route('user.devices.show', $device->id) }}" class="btn btn-sm btn-outline-primary">
                            Manage Channels <i class="ti ti-arrow-right ms-1"></i>
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
        $(document).on('change', '.pin-toggle-switch', function () {
            let deviceId = $(this).data('device-id');
            let pinId = $(this).data('pin-id');
            let isChecked = $(this).is(':checked');
            let state = isChecked ? '1' : '0';

            $.ajax({
                url: '/devices/' + deviceId + '/pins/' + pinId + '/toggle',
                type: 'POST',
                data: { state: state, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1200, icon: 'success', title: res.message || 'State updated' });
                },
                error: function () {
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update switch' });
                }
            });
        });
    </script>
@endpush
