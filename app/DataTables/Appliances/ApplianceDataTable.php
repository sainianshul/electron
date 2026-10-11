<?php

namespace App\DataTables\Appliances;

use App\Models\Appliance;
use Yajra\DataTables\Services\DataTable;

class ApplianceDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── Item / Appliance Info ──────────────────────────────────
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->editColumn('name', function (Appliance $appliance) {
                $showUrl = route('admin.appliances.show', $appliance->id);
                
                // Icon or Image
                if ($appliance->image_url) {
                    $avatar = '<span class="avatar avatar-sm me-2" style="background-image: url(' . e($appliance->image_url) . ')"></span>';
                } else {
                    $iconClass = $appliance->icon ?: 'ti ti-plug';
                    $avatar = '<span class="avatar avatar-sm bg-purple-lt me-2"><i class="' . e($iconClass) . ' fs-3"></i></span>';
                }

                $signalText = '<small class="text-secondary d-block font-monospace">' . e($appliance->signal_type) . ' • ' . e($appliance->type) . '</small>';

                return '
                    <div class="d-flex align-items-center">
                        <a href="' . $showUrl . '" class="d-block">' . $avatar . '</a>
                        <div>
                            <a href="' . $showUrl . '" class="fw-semibold text-reset text-decoration-none">' . e($appliance->name) . '</a>
                            ' . $signalText . '
                        </div>
                    </div>
                ';
            })

            // ── Type (Input / Output) ─────────────────────────────────
            ->editColumn('type', function (Appliance $appliance) {
                if ($appliance->type === Appliance::TYPE_INPUT) {
                    return '<span class="badge bg-teal-lt">Input</span>';
                }
                return '<span class="badge bg-blue-lt">Output</span>';
            })

            // ── Signal Type ───────────────────────────────────────────
            ->editColumn('signal_type', function (Appliance $appliance) {
                return match ($appliance->signal_type) {
                    Appliance::SIGNAL_PWM => '<span class="badge bg-azure-lt">PWM</span>',
                    Appliance::SIGNAL_ANALOG => '<span class="badge bg-orange-lt">Analog</span>',
                    default => '<span class="badge bg-cyan-lt">Digital</span>',
                };
            })

            // ── Supported Pins ────────────────────────────────────────
            ->addColumn('supported_pin_types', function (Appliance $appliance) {
                $pins = $appliance->supported_pin_types ?? [];
                if (empty($pins)) {
                    return '<span class="text-secondary small">Any</span>';
                }
                $badges = array_map(function ($pin) {
                    return '<span class="badge bg-dark-lt text-uppercase font-monospace me-1">' . e($pin) . '</span>';
                }, $pins);

                return implode('', $badges);
            })

            // ── Status ────────────────────────────────────────────────
            ->addColumn('status', function (Appliance $appliance) {
                $color = $appliance->status_color;
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $appliance->id . '" data-status="' . $appliance->status . '">
                        <i class="' . $appliance->status_icon . ' me-1"></i>' . e($appliance->status_name) . '
                    </a>
                ';
            })

            // ── Created Date ──────────────────────────────────────────
            ->editColumn('created_at', function (Appliance $appliance) {
                return '<div class="text-secondary">' . $appliance->created_at->format('d M Y') . '</div>';
            })

            // ── Actions ───────────────────────────────────────────────
            ->addColumn('actions', function (Appliance $appliance) {
                $viewUrl = route('admin.appliances.show', $appliance->id);
                $editUrl = route('admin.appliances.edit', $appliance->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View Item">
                            <i class="ti ti-eye"></i>
                        </a>

                        <a href="' . $editUrl . '"
                            class="btn btn-icon btn-sm btn-outline-warning"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </a>

                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $appliance->id . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'name',
                'type',
                'signal_type',
                'supported_pin_types',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(Appliance $model)
    {
        $query = $model->newQuery()->latest();

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('type')) {
            $query->where('type', request('type'));
        }

        if (request()->filled('signal_type')) {
            $query->where('signal_type', request('signal_type'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Appliances_' . date('Y_m_d_His');
    }
}
