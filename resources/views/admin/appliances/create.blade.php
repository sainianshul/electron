@extends('admin.layouts.app')

@section('title', 'Add Appliance')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['url' => route('admin.appliances.index'), 'label' => 'Appliances'],
                    ['label' => 'Add Appliance'],
                ]" />
                <h2 class="page-title">Add New Appliance</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.appliances.index') }}" class="btn">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('admin.appliances.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card-body">
                <div class="row g-3">
                    
                    {{-- Name --}}
                    <div class="col-md-6">
                        <label class="form-label required">Appliance Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Ceiling Fan" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type (Input / Output) --}}
                    <div class="col-md-6">
                        <label class="form-label required">Type</label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            @foreach (\App\Models\Appliance::getTypeList() as $val => $label)
                                <option value="{{ $val }}" {{ old('type', \App\Models\Appliance::TYPE_OUTPUT) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Signal Type --}}
                    <div class="col-md-6">
                        <label class="form-label required">Signal Type</label>
                        <select name="signal_type" class="form-select @error('signal_type') is-invalid @enderror" required>
                            @foreach (\App\Models\Appliance::getSignalTypeList() as $val => $label)
                                <option value="{{ $val }}" {{ old('signal_type', \App\Models\Appliance::SIGNAL_DIGITAL) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('signal_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach (\App\Models\Appliance::getStatusList() as $val => $label)
                                <option value="{{ $val }}" {{ old('status', \App\Models\Appliance::STATUS_ACTIVE) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Supported Pins --}}
                    <div class="col-md-12">
                        <label class="form-label">Supported Pin Types</label>
                        <div class="d-flex gap-4 mt-1">
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="supported_pin_types[]" value="DIGITAL" {{ in_array('DIGITAL', old('supported_pin_types', ['DIGITAL'])) ? 'checked' : '' }}>
                                <span class="form-check-label">Digital</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="supported_pin_types[]" value="PWM" {{ in_array('PWM', old('supported_pin_types', [])) ? 'checked' : '' }}>
                                <span class="form-check-label">PWM</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="supported_pin_types[]" value="ANALOG" {{ in_array('ANALOG', old('supported_pin_types', [])) ? 'checked' : '' }}>
                                <span class="form-check-label">Analog</span>
                            </label>
                        </div>
                    </div>

                    {{-- Icon Class --}}
                    <div class="col-md-6">
                        <label class="form-label">Icon</label>
                        <div class="input-group">
                            <input type="text" name="icon" id="icon-input" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', 'ti ti-bulb') }}" placeholder="e.g. ti ti-bulb">
                            <span class="input-group-text"><i id="icon-preview" class="ti ti-bulb fs-3"></i></span>
                        </div>
                        <div class="mt-2 small text-muted">
                            Suggestions: 
                            <a href="javascript:void(0)" class="badge bg-light text-secondary icon-pick me-1" data-icon="ti ti-bulb">Bulb</a>
                            <a href="javascript:void(0)" class="badge bg-light text-secondary icon-pick me-1" data-icon="ti ti-fan">Fan</a>
                            <a href="javascript:void(0)" class="badge bg-light text-secondary icon-pick me-1" data-icon="ti ti-plug">Plug</a>
                            <a href="javascript:void(0)" class="badge bg-light text-secondary icon-pick me-1" data-icon="ti ti-device-tv">TV</a>
                            <a href="javascript:void(0)" class="badge bg-light text-secondary icon-pick me-1" data-icon="ti ti-air-conditioning">AC</a>
                            <a href="javascript:void(0)" class="badge bg-light text-secondary icon-pick me-1" data-icon="ti ti-droplet">Pump</a>
                        </div>
                        @error('icon')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Default State --}}
                    <div class="col-md-6">
                        <label class="form-label">Default State</label>
                        <input type="text" name="default_state" class="form-control @error('default_state') is-invalid @enderror" value="{{ old('default_state', '0') }}" placeholder="e.g. 0">
                        @error('default_state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Image Upload --}}
                    <div class="col-md-12">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.appliances.index') }}" class="btn btn-link link-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Appliance</button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#icon-input').on('input', function () {
                let icon = $(this).val() || 'ti ti-plug';
                $('#icon-preview').attr('class', icon + ' fs-3');
            });

            $('.icon-pick').on('click', function () {
                let icon = $(this).data('icon');
                $('#icon-input').val(icon);
                $('#icon-preview').attr('class', icon + ' fs-3');
            });
        });
    </script>
@endpush
