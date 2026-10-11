@extends('admin.layouts.app')

@section('title', 'Appliance Details')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['url' => route('admin.appliances.index'), 'label' => 'Appliances'],
                    ['label' => $appliance->name],
                ]" />
                <h2 class="page-title d-flex align-items-center gap-2">
                    @if($appliance->image_url)
                        <span class="avatar" style="background-image: url('{{ $appliance->image_url }}')"></span>
                    @else
                        <span class="avatar bg-purple-lt">
                            <i class="{{ $appliance->icon ?: 'ti ti-plug' }} fs-2"></i>
                        </span>
                    @endif
                    {{ $appliance->name }}
                </h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.appliances.edit', $appliance->id) }}" class="btn btn-outline-warning">
                    <i class="ti ti-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('admin.appliances.index') }}" class="btn">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row row-cards">

        {{-- Left Column: Specs Card --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-info-circle me-2 text-primary"></i>Specification</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">

                        <div class="datagrid-item">
                            <div class="datagrid-title">Direction Type</div>
                            <div class="datagrid-content">
                                @if($appliance->type === \App\Models\Appliance::TYPE_INPUT)
                                    <span class="badge bg-teal-lt"><i class="ti ti-arrow-down-circle me-1"></i>Input (Sensor)</span>
                                @else
                                    <span class="badge bg-blue-lt"><i class="ti ti-power me-1"></i>Output (Appliance)</span>
                                @endif
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Signal Type</div>
                            <div class="datagrid-content">
                                @if($appliance->signal_type === \App\Models\Appliance::SIGNAL_PWM)
                                    <span class="badge bg-azure-lt"><i class="ti ti-wave-sine me-1"></i>PWM (Dimmer)</span>
                                @elseif($appliance->signal_type === \App\Models\Appliance::SIGNAL_ANALOG)
                                    <span class="badge bg-orange-lt"><i class="ti ti-activity me-1"></i>Analog</span>
                                @else
                                    <span class="badge bg-cyan-lt"><i class="ti ti-toggle-right me-1"></i>Digital</span>
                                @endif
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">
                                <span class="badge badge-outline text-{{ $appliance->status_color }}">
                                    <i class="{{ $appliance->status_icon }} me-1"></i>{{ $appliance->status_name }}
                                </span>
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Default State</div>
                            <div class="datagrid-content font-monospace">
                                {{ $appliance->default_state }}
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Supported Pin Types</div>
                            <div class="datagrid-content">
                                @forelse($appliance->supported_pin_types ?? [] as $pType)
                                    <span class="badge bg-dark-lt font-monospace text-uppercase me-1">{{ $pType }}</span>
                                @empty
                                    <span class="text-secondary small">Any</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="datagrid-item">
                            <div class="datagrid-title">Added On</div>
                            <div class="datagrid-content">
                                {{ $appliance->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Active Devices Using this Appliance --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h3 class="card-title">
                            <i class="ti ti-cpu me-2 text-primary"></i>Assigned Devices & Pins
                        </h3>
                        <span class="badge bg-purple-lt">{{ $appliance->pins->count() }} Active Usages</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>Owner</th>
                                <th>Pin</th>
                                <th>Custom Name</th>
                                <th>Current State</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appliance->pins as $pin)
                                <tr>
                                    <td>
                                        @if($pin->device)
                                            <a href="{{ route('admin.devices.show', $pin->device->id) }}" class="fw-semibold text-reset">
                                                {{ $pin->device->name }}
                                            </a>
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $pin->device->user->name ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="badge bg-dark text-white font-monospace">
                                            {{ $pin->pin_name }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $pin->custom_name ?: 'Default' }}
                                    </td>
                                    <td>
                                        @if($pin->pin_type === 'PWM')
                                            <span class="badge bg-purple-lt">{{ $pin->current_state }} / 255</span>
                                        @else
                                            @if($pin->current_state == '1')
                                                <span class="badge bg-success"><i class="ti ti-power me-1"></i>ON</span>
                                            @else
                                                <span class="badge bg-secondary-lt"><i class="ti ti-power me-1"></i>OFF</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        This appliance is not yet assigned to any device pins.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection
