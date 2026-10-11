@extends('admin.layouts.app')

@section('title', 'Edit Device')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['url' => route('user.devices.index'), 'label' => 'My Devices'],
                    ['label' => 'Edit Device'],
                ]" />
                <h2 class="page-title">Edit Device: {{ $device->name }}</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('user.devices.show', $device->id) }}" class="btn">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('user.devices.update', $device->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <div class="row g-3">
                    
                    {{-- Device Name --}}
                    <div class="col-md-6">
                        <label class="form-label required">Device Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $device->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- MAC Address --}}
                    <div class="col-md-6">
                        <label class="form-label required">MAC Address</label>
                        <input type="text" name="mac_address" class="form-control font-monospace text-uppercase @error('mac_address') is-invalid @enderror" value="{{ old('mac_address', $device->mac_address) }}" required>
                        @error('mac_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- IP Address --}}
                    <div class="col-md-6">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control font-monospace @error('ip_address') is-invalid @enderror" value="{{ old('ip_address', $device->ip_address) }}" placeholder="e.g. 192.168.1.100">
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-md-6">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $device->description) }}" placeholder="Optional description...">
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('user.devices.index') }}" class="btn btn-link link-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Device</button>
            </div>
        </form>
    </div>

@endsection
