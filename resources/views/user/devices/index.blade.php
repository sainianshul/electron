@extends('admin.layouts.app')

@section('title', 'My Devices')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'My Devices'],
                ]" />
                <h2 class="page-title">My Smart Devices</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('user.devices.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Device
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>MAC Address</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Channels</th>
                        <th>Added Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm bg-primary-lt me-2"><i class="ti ti-cpu fs-3"></i></span>
                                    <div>
                                        <a href="{{ route('user.devices.show', $device->id) }}" class="fw-semibold text-reset text-decoration-none">
                                            {{ $device->name }}
                                        </a>
                                        @if($device->description)
                                            <div class="text-secondary small text-truncate" style="max-width: 200px;">{{ $device->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-purple-lt font-monospace text-uppercase">{{ $device->mac_address }}</span>
                            </td>
                            <td>
                                <span class="text-secondary font-monospace">{{ $device->ip_address ?? '—' }}</span>
                            </td>
                            <td>
                                @if($device->is_online)
                                    <span class="badge bg-success-lt"><i class="ti ti-wifi me-1"></i>Online</span>
                                @else
                                    <span class="badge bg-secondary-lt"><i class="ti ti-wifi-off me-1"></i>Offline</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-azure-lt">{{ $device->pins->where('is_assigned', true)->count() }} / {{ $device->pins->count() }} Appliances</span>
                            </td>
                            <td>
                                <span class="text-secondary">{{ $device->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('user.devices.show', $device->id) }}" class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Manage Appliances">
                                        <i class="ti ti-plug"></i>
                                    </a>
                                    <a href="{{ route('user.devices.edit', $device->id) }}" class="btn btn-icon btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Edit">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger btn-delete" data-id="{{ $device->id }}" data-bs-toggle="tooltip" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="ti ti-cpu text-muted fs-1 d-block mb-2"></i>
                                No devices registered yet. <a href="{{ route('user.devices.create') }}">Add your first board.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
            <div class="card-footer">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        $(document).on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Delete Device?',
                text: 'This will delete the board and all configured pin channels.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.post('/devices/' + id, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                    .done(function () {
                        location.reload();
                    })
                    .fail(function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Something went wrong' });
                    });
            });
        });
    </script>
@endpush
