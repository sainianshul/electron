<?php

namespace App\DataTables\Devices;

use App\Models\Device;
use Yajra\DataTables\Services\DataTable;

class DeviceDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── Device Info ───────────────────────────────────────────
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('devices.name', 'like', "%{$keyword}%")
                      ->orWhere('devices.mac_address', 'like', "%{$keyword}%")
                      ->orWhere('devices.ip_address', 'like', "%{$keyword}%")
                      ->orWhereHas('user', function ($uq) use ($keyword) {
                          $uq->where('name', 'like', "%{$keyword}%")
                             ->orWhere('phone', 'like', "%{$keyword}%");
                      });
                });
            })
            ->editColumn('name', function (Device $device) {
                $showUrl = route('admin.devices.show', $device->id);
                $desc = $device->description ? '<small class="text-secondary d-block text-truncate" style="max-width: 180px;">' . e($device->description) . '</small>' : '';

                return '
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm bg-primary-lt me-2">
                            <i class="ti ti-cpu fs-3"></i>
                        </span>
                        <div>
                            <a href="' . $showUrl . '" class="fw-semibold text-reset text-decoration-none">' . e($device->name) . '</a>
                            ' . $desc . '
                        </div>
                    </div>
                ';
            })

            // ── Owner / User ──────────────────────────────────────────
            ->addColumn('user', function (Device $device) {
                if (!$device->user) {
                    return '<span class="text-secondary">—</span>';
                }
                $userUrl = route('admin.users.show', $device->user->id);
                return '
                    <div>
                        <a href="' . $userUrl . '" class="text-reset fw-medium text-decoration-none">
                            ' . e($device->user->name) . '
                        </a>
                        <div class="text-secondary small">' . e($device->user->phone ?? '') . '</div>
                    </div>
                ';
            })

            // ── MAC Address ───────────────────────────────────────────
            ->editColumn('mac_address', function (Device $device) {
                return '<span class="badge bg-purple-lt font-monospace text-uppercase"><i class="ti ti-binary me-1"></i>' . e($device->mac_address) . '</span>';
            })

            // ── IP Address ────────────────────────────────────────────
            ->editColumn('ip_address', function (Device $device) {
                return $device->ip_address 
                    ? '<span class="text-secondary font-monospace">' . e($device->ip_address) . '</span>'
                    : '<span class="text-secondary">—</span>';
            })

            // ── Online / Connectivity ─────────────────────────────────
            ->editColumn('is_online', function (Device $device) {
                if ($device->is_online) {
                    return '<span class="badge bg-success-lt"><i class="ti ti-wifi me-1"></i>Online</span>';
                }
                return '<span class="badge bg-secondary-lt"><i class="ti ti-wifi-off me-1"></i>Offline</span>';
            })

            // ── Status ────────────────────────────────────────────────
            ->editColumn('status', function (Device $device) {
                $color = $device->status_color;
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $device->id . '" data-status="' . $device->status . '">
                        <i class="' . $device->status_icon . ' me-1"></i>' . e($device->status_name) . '
                    </a>
                ';
            })

            // ── Created Date ──────────────────────────────────────────
            ->editColumn('created_at', function (Device $device) {
                return '<div class="text-secondary">' . $device->created_at->format('d M Y') . '</div>';
            })

            // ── Actions ───────────────────────────────────────────────
            ->addColumn('actions', function (Device $device) {
                $viewUrl = route('admin.devices.show', $device->id);
                $editUrl = route('admin.devices.edit', $device->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View Device">
                            <i class="ti ti-eye"></i>
                        </a>

                        <a href="' . $editUrl . '"
                            class="btn btn-icon btn-sm btn-outline-warning"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </a>

                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $device->id . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'name',
                'user',
                'mac_address',
                'ip_address',
                'is_online',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(Device $model)
    {
        $query = $model->newQuery()
            ->with(['user', 'pins'])
            ->select('devices.*')
            ->latest();

        if (request()->filled('status')) {
            $query->where('devices.status', request('status'));
        }

        if (request()->filled('is_online')) {
            $query->where('devices.is_online', request('is_online'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Devices_' . date('Y_m_d_His');
    }
}
