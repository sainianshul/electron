<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electron - Welcome</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
</head>
<body class="d-flex flex-column" data-bs-theme="light">
    <div class="page page-center">
        <div class="container-tight py-4">
            <div class="text-center mb-4">
                <a href="/" class="navbar-brand navbar-brand-autodark">
                    <h2><i class="ti ti-cpu text-primary"></i> Electron</h2>
                </a>
            </div>
            
            <div class="card card-md">
                <div class="card-body text-center py-5">
                    <h1 class="mt-4">Welcome to Electron</h1>
                    <p class="text-muted mb-4">Please log in to access the secure portal and manage the system.</p>
                    
                    <div class="mt-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/admin/dashboard') }}" class="btn btn-primary w-100 mb-3">
                                    <i class="ti ti-dashboard me-2"></i> Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-3">
                                    <i class="ti ti-login me-2"></i> Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">
                                        <i class="ti ti-user-plus me-2"></i> Register
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="text-center text-muted mt-3">
                &copy; {{ date('Y') }} Electron. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
