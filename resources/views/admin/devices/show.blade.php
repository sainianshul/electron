@extends('admin.layouts.app')

@section('title', 'Device Details')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['url' => route('admin.devices.index'), 'label' => 'Devices'],
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
                <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-outline-warning">
                    <i class="ti ti-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.devices.index') }}" class="btn">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row row-cards">

        {{-- Left Column: Device & Owner Details --}}
        <div class="col-lg-4">

            {{-- Device Specs Card --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2 text-primary"></i>Device Information</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Hardware Platform</div>
                            <div class="datagrid-content">
                                <span class="badge bg-blue-lt">NodeMCU ESP8266</span>
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Connectivity Status</div>
                            <div class="datagrid-content">
                                @if($device->is_online)
                                    <span class="badge bg-success-lt"><i class="ti ti-wifi me-1"></i>Online</span>
                                @else
                                    <span class="badge bg-secondary-lt"><i class="ti ti-wifi-off me-1"></i>Offline</span>
                                @endif
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">System Status</div>
                            <div class="datagrid-content">
                                <span class="badge badge-outline text-{{ $device->status_color }}">
                                    <i class="{{ $device->status_icon }} me-1"></i>{{ $device->status_name }}
                                </span>
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">MAC Address / Chip ID</div>
                            <div class="datagrid-content font-monospace fw-bold text-uppercase">
                                {{ $device->mac_address }}
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Local IP Address</div>
                            <div class="datagrid-content font-monospace">
                                {{ $device->ip_address ?? '—' }}
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Last Ping / Heartbeat</div>
                            <div class="datagrid-content">
                                {{ $device->last_seen_at ? $device->last_seen_at->format('d M Y, H:i') : 'Never' }}
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Added On</div>
                            <div class="datagrid-content">
                                {{ $device->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>

                    @if($device->description)
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-secondary small fw-bold mb-1">DESCRIPTION / LOCATION</div>
                            <div class="text-muted">{{ $device->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Device Owner Card --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-user me-2 text-primary"></i>Device Owner</h3>
                </div>
                <div class="card-body">
                    @if($device->user)
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar avatar-md me-3">{!! $device->user->avatar_html !!}</span>
                            <div>
                                <h4 class="m-0">{{ $device->user->name }}</h4>
                                <div class="text-secondary small">{{ $device->user->phone ?? $device->user->email ?? '—' }}</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.users.show', $device->user->id) }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="ti ti-external-link me-1"></i>View User Profile
                        </a>
                    @else
                        <div class="text-secondary">No owner assigned.</div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Right Column: Configured Pins / Channels --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h3 class="card-title">
                            <i class="ti ti-circuit-switch me-2 text-primary"></i>Hardware Pins & Channels
                        </h3>
                        <span class="badge bg-purple-lt">{{ $device->pins->count() }} Configured Pins</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Board Pin</th>
                                <th>GPIO</th>
                                <th>Mode</th>
                                <th>Assigned Appliance</th>
                                <th>Current State</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($device->pins as $pin)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark text-white font-monospace fs-4 px-2 py-1">
                                            {{ $pin->pin_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-secondary">GPIO {{ $pin->gpio_number }}</span>
                                    </td>
                                    <td>
                                        @if($pin->pin_type === 'PWM')
                                            <span class="badge bg-azure-lt"><i class="ti ti-wave-sine me-1"></i>PWM (Dimmer)</span>
                                        @else
                                            <span class="badge bg-cyan-lt"><i class="ti ti-toggle-right me-1"></i>DIGITAL (Relay)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pin->appliance)
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $pin->appliance->icon ?? 'ti ti-device-heart-monitor' }} text-primary me-2 fs-3"></i>
                                                <div>
                                                    <div class="fw-semibold">{{ $pin->custom_name ?? $pin->appliance->name }}</div>
                                                    <small class="text-secondary">{{ $pin->appliance->name }}</small>
                                                </div>
                                            </div>
                                        @elseif($pin->custom_name)
                                            <div class="fw-semibold">{{ $pin->custom_name }}</div>
                                        @else
                                            <span class="text-secondary italic">Unassigned (Available)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pin->pin_type === 'PWM')
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-purple-lt">{{ $pin->current_state ?? '0' }} / 255</span>
                                            </div>
                                        @else
                                            @if($pin->current_state == '1')
                                                <span class="badge bg-success"><i class="ti ti-power me-1"></i>ON</span>
                                            @else
                                                <span class="badge bg-secondary-lt"><i class="ti ti-power me-1"></i>OFF</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($pin->is_assigned)
                                            <span class="badge bg-success-lt">Connected</span>
                                        @else
                                            <span class="badge bg-secondary-lt">Ready</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        No pins initialized for this device.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light-subtle text-secondary small">
                    <i class="ti ti-info-circle me-1"></i>Pins <strong>D1, D2, D5, D6</strong> operate as Digital Relays, and Pin <strong>D7</strong> operates as PWM channel for fan or dimming.
                </div>
            </div>
        </div>

    </div>

@endsection
