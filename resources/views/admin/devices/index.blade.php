@extends('admin.layouts.app')

@section('title', 'Devices')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Devices'],
                ]" />
                <h2 class="page-title">Devices (Smart Home)</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.devices.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Add Device
                </a>
            </div>
        </div>
    </div>

    <div class="card">

        {{-- Toolbar --}}
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-3">

                {{-- Search --}}
                <div class="input-icon">
                    <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                    <input type="text" id="dt-search" class="form-control" style="width: 260px;"
                        placeholder="Search devices..." />
                </div>

                {{-- Right Controls --}}
                <div class="d-flex align-items-center gap-2">

                    {{-- Refresh Button --}}
                    <button type="button" class="btn btn-icon btn-ghost-secondary" id="refresh-table-btn"
                        data-bs-toggle="tooltip" title="Refresh">
                        <i class="ti ti-refresh"></i>
                    </button>

                    {{-- Online Filter --}}
                    <select id="filter-online" class="form-select" style="width: 140px;">
                        <option value="">All Connectivity</option>
                        <option value="1">Online</option>
                        <option value="0">Offline</option>
                    </select>

                    {{-- Status Filter --}}
                    <select id="filter-status" class="form-select" style="width: 140px;">
                        <option value="">All Status</option>
                        @foreach (\App\Models\Device::getStatusList() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            {{-- Loading Spinner --}}
            <div id="devices-loader" class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading…</span>
                </div>
            </div>

            {{-- Table --}}
            <div id="devices-table-wrapper" class="table-responsive d-none">
                <table id="devices-table" class="table table-vcenter w-100">
                    <thead>
                        <tr>
                            <th class="w-1">S.No</th>
                            <th>Device Info</th>
                            <th>Owner</th>
                            <th>MAC Address</th>
                            <th>IP Address</th>
                            <th>Connectivity</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @include('admin.layouts.partials._table-empty', [
                'id' => 'devices-empty',
                'title' => 'No devices found',
                'subtitle' => 'Try adjusting your search or filters to find what you are looking for.'
            ])

        </div>
    </div>

    {{-- Status Update Modal --}}
    <div class="modal modal-blur fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="status-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="status-modal-id">
                        <div class="mb-3">
                            <label class="form-label required">Select Status</label>
                            <select class="form-select" id="status-modal-select" required>
                                @foreach (\App\Models\Device::getStatusList() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
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

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')

    <script>
        $(function () {

            let table = $('#devices-table').DataTable({
                processing: false,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.devices.data') }}',
                    data: function (d) {
                        d.status = $('#filter-status').val();
                        d.is_online = $('#filter-online').val();
                    }
                },

                columns: [
                    { data: null, name: 'id', render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }, orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'user', name: 'user', orderable: false, searchable: false },
                    { data: 'mac_address', name: 'mac_address' },
                    { data: 'ip_address', name: 'ip_address' },
                    { data: 'is_online', name: 'is_online' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' },
                ],

                order: [[7, 'desc']], // Order by created_at desc
                pageLength: 15,
                lengthMenu: [[10, 15, 25, 50], [10, 15, 25, 50]],

                dom:
                    "<'row'<'col-12'tr>>" +
                    "<'row align-items-center mt-3 pt-3 flex-nowrap'" +
                    "<'col-sm-12 col-md-5'i>" +
                    "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",

                language: {
                    emptyTable: ' ',
                    zeroRecords: ' ',
                    loadingRecords: ' ',
                    info: 'Showing _START_–_END_ of _TOTAL_ devices',
                    infoEmpty: 'No devices to show',
                    infoFiltered: '(filtered from _MAX_)',
                    lengthMenu: 'Show _MENU_',
                    paginate: {
                        previous: '<i class="ti ti-chevron-left"></i>',
                        next: '<i class="ti ti-chevron-right"></i>',
                    },
                },

                initComplete: function () {
                    $('#devices-loader').remove();
                    let total = this.api().page.info().recordsDisplay;
                    if (total === 0) {
                        $('#devices-table-wrapper').addClass('d-none');
                        $('#devices-empty').removeClass('d-none');
                    } else {
                        $('#devices-empty').addClass('d-none');
                        $('#devices-table-wrapper').removeClass('d-none');
                    }
                },

                drawCallback: function () {
                    if ($('#devices-loader').length === 0) {
                        let total = this.api().page.info().recordsDisplay;
                        if (total === 0) {
                            $('#devices-table-wrapper').addClass('d-none');
                            $('#devices-empty').removeClass('d-none');
                        } else {
                            $('#devices-empty').addClass('d-none');
                            $('#devices-table-wrapper').removeClass('d-none');
                        }
                    }
                    $('[data-bs-toggle="tooltip"]').tooltip({ trigger: 'hover' });
                }
            });

            // Search
            let searchTimer;
            $('#dt-search').on('input', function () {
                clearTimeout(searchTimer);
                let query = $(this).val();
                searchTimer = setTimeout(function () {
                    table.search(query).draw();
                }, 400);
            });

            // Filters
            $('#filter-status, #filter-online').on('change', function () {
                table.ajax.reload();
            });

            // Refresh Button
            $('#refresh-table-btn').on('click', function () {
                $(this).tooltip('hide');
                table.ajax.reload(null, false);
                Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Refreshed' });
            });

            // Open Status Modal
            $(document).on('click', '.status-modal-btn', function (e) {
                e.preventDefault();
                let id = $(this).data('id');
                let currentStatus = $(this).data('status');
                
                $('#status-modal-id').val(id);
                $('#status-modal-select').val(currentStatus);
                $('#status-modal').modal('show');
            });

            // Submit Status Modal
            $('#status-modal-form').on('submit', function (e) {
                e.preventDefault();
                let id = $('#status-modal-id').val();
                let status = $('#status-modal-select').val();
                let submitBtn = $(this).find('button[type="submit"]');
                let originalText = submitBtn.html();
                
                submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...').prop('disabled', true);

                $.ajax({
                    url: '/admin/devices/' + id + '/status',
                    type: 'POST',
                    data: { status: status, _token: '{{ csrf_token() }}' },
                    success: function () {
                        $('#status-modal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Status updated' });
                    },
                    error: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                    },
                    complete: function () {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Delete
            $(document).on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Device?',
                    text: 'This will also delete all associated device pins and settings.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                    buttonsStyling: false,
                }).then(function (result) {
                    if (!result.isConfirmed) return;
                    $.post('/admin/devices/' + id, { _method: 'DELETE', _token: '{{ csrf_token() }}' })
                        .done(function () {
                            table.ajax.reload(null, false);
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Device deleted' });
                        })
                        .fail(function () {
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Something went wrong' });
                        });
                });
            });

        });
    </script>
@endpush
