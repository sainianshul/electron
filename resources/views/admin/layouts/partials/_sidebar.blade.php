<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="light">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark">
            <a href="{{ auth()->check() && auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}">
                Electronia
            </a>
        </h1>
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item d-flex me-3">
                <a href="#" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip"
		   data-bs-placement="bottom" onclick="localStorage.setItem('data-bs-theme', 'dark'); window.location.reload();">
                    <i class="ti ti-moon fs-2"></i>
                </a>
                <a href="#" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip"
		   data-bs-placement="bottom" onclick="localStorage.setItem('data-bs-theme', 'light'); window.location.reload();">
                    <i class="ti ti-sun fs-2"></i>
                </a>
            </div>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm">{!! auth()->user()->avatar_html ?? '<i class="ti ti-user"></i>' !!}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.profile.index') : '#' }}" class="dropdown-item">Profile</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                
                @if(auth()->check() && auth()->user()->isAdmin())
                    {{-- ========================================= --}}
                    {{-- ADMIN NAVIGATION                          --}}
                    {{-- ========================================= --}}
                    
                    {{-- Dashboard --}}
                    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-home"></i>
                            </span>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>
                    
                    {{-- Users Management --}}
                    <li class="nav-item mt-3 mb-1">
                        <div class="nav-link text-uppercase text-muted fs-8 fw-bold">User Management</div>
                    </li>
                    
                    <li class="nav-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-users"></i>
                            </span>
                            <span class="nav-link-title">All Users</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.users.blocked') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.users.blocked') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-user-off"></i>
                            </span>
                            <span class="nav-link-title">Blocked Users</span>
                        </a>
                    </li>

                    {{-- Smart Home Management (Admin) --}}
                    <li class="nav-item mt-3 mb-1">
                        <div class="nav-link text-uppercase text-muted fs-8 fw-bold">Smart Home System</div>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.devices.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.devices.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-cpu"></i>
                            </span>
                            <span class="nav-link-title">All Devices</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.appliances.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.appliances.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-plug"></i>
                            </span>
                            <span class="nav-link-title">Appliances Catalog</span>
                        </a>
                    </li>

                    {{-- Trash --}}
                    <li class="nav-item mt-3 mb-1">
                        <div class="nav-link text-uppercase text-muted fs-8 fw-bold">System Trash</div>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.users.deleted') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.users.deleted') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-trash"></i>
                            </span>
                            <span class="nav-link-title">Deleted Users</span>
                        </a>
                    </li>

                @else
                    {{-- ========================================= --}}
                    {{-- REGULAR USER NAVIGATION                   --}}
                    {{-- ========================================= --}}

                    {{-- User Dashboard --}}
                    <li class="nav-item {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('user.dashboard') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-home"></i>
                            </span>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>

                    {{-- User Smart Devices & Appliances --}}
                    <li class="nav-item mt-3 mb-1">
                        <div class="nav-link text-uppercase text-muted fs-8 fw-bold">My Smart Home</div>
                    </li>

                    <li class="nav-item {{ request()->routeIs('user.appliances.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('user.appliances.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-plug"></i>
                            </span>
                            <span class="nav-link-title">My Appliances</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('user.devices.index') || request()->routeIs('user.devices.show') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('user.devices.index') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-cpu"></i>
                            </span>
                            <span class="nav-link-title">My Devices</span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('user.devices.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('user.devices.create') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <i class="ti ti-plus"></i>
                            </span>
                            <span class="nav-link-title">Add Device</span>
                        </a>
                    </li>

                @endif

            </ul>
        </div>
    </div>
</aside>