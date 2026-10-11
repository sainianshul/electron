@extends('admin.layouts.app')

@section('title', 'Add Device')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['url' => route('admin.devices.index'), 'label' => 'Devices'],
                    ['label' => 'Add Device'],
                ]" />
                <h2 class="page-title">Add New Device</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.devices.index') }}" class="btn">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('admin.devices.store') }}" method="POST">
            @csrf
            
            <div class="card-body">
                <div class="row g-3">
                    
                    {{-- Owner / User --}}
                    <div class="col-md-6">
                        <label class="form-label required">Device Owner</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Select Owner</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->phone ?? $user->email ?? $user->id }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Device Name --}}
                    <div class="col-md-6">
                        <label class="form-label required">Device Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Living Room" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- MAC Address --}}
                    <div class="col-md-6">
                        <label class="form-label required">MAC Address</label>
                        <input type="text" name="mac_address" class="form-control font-monospace text-uppercase @error('mac_address') is-invalid @enderror" value="{{ old('mac_address') }}" placeholder="e.g. AA:BB:CC:DD:EE:FF" required>
                        @error('mac_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- IP Address --}}
                    <div class="col-md-6">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control font-monospace @error('ip_address') is-invalid @enderror" value="{{ old('ip_address') }}" placeholder="e.g. 192.168.1.100">
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach (\App\Models\Device::getStatusList() as $val => $label)
                                <option value="{{ $val }}" {{ old('status', \App\Models\Device::STATUS_ACTIVE) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}" placeholder="Optional description...">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('admin.devices.index') }}" class="btn btn-link link-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Device</button>
            </div>
        </form>
    </div>

@endsection
