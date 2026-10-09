<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Sign In — Electron Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    <style>
        body {
            /* Sleek electronics/circuit background */
            background-color: #0f172a;
            background-image: radial-gradient(circle at 15% 50%, rgba(56, 189, 248, 0.08), transparent 25%),
                              radial-gradient(circle at 85% 30%, rgba(99, 102, 241, 0.08), transparent 25%);
            background-size: cover;
            color: #f8fafc;
        }
        .page-center {
            position: relative;
            z-index: 1;
        }
        /* Circuit board pattern overlay */
        .page-center::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            opacity: 0.03;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+CjxwYXRoIGQ9Ik0wIDIwaDQwdk0yMCAwaHY0MCIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiLz4KPC9zdmc+');
            z-index: -1;
        }
        .card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border-radius: 12px;
        }
        .navbar-brand h1 {
            color: #38bdf8;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #38bdf8 0%, #2563eb 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
        .form-control {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
        }
        .form-control:focus {
            background-color: rgba(15, 23, 42, 0.8);
            border-color: #38bdf8;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25);
            color: #f8fafc;
        }
        .input-group-text {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: none;
        }
        label.form-label {
            color: #cbd5e1;
        }
        .text-muted {
            color: #94a3b8 !important;
        }
    </style>
</head>
<body class="d-flex flex-column" data-bs-theme="light">

    <div class="page page-center">
        <div class="container container-tight py-4">

            <div class="text-center mb-4">
                <a href="/" class="navbar-brand navbar-brand-autodark text-decoration-none">
                    <h1><i class="ti ti-cpu"></i> Electron</h1>
                </a>
            </div>

            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h3 text-center mb-4 text-white">System Authentication</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger bg-danger text-white border-0" role="alert">
                            @foreach ($errors->all() as $error)
                                <div><i class="ti ti-alert-circle me-1"></i> {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" autocomplete="off" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="admin@electron.com" autocomplete="off">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Access Code (Password)</label>
                            <div class="input-group input-group-flat">
                                <input type="password" name="password" id="password"
                                       class="form-control border-end-0 @error('password') is-invalid @enderror"
                                       placeholder="Enter your password" autocomplete="off">
                                <span class="input-group-text bg-transparent">
                                    <a href="#" class="link-secondary text-decoration-none" id="toggle-password" title="Show password"
                                       data-bs-toggle="tooltip">
                                        <i class="ti ti-eye" id="eye-icon"></i>
                                    </a>
                                </span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input" name="remember" value="1"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <span class="form-check-label">Remember me on this terminal</span>
                            </label>
                        </div>

                        <div class="form-footer mt-2">
                            <button type="submit" class="btn btn-primary w-100" id="submit-btn">
                                <i class="ti ti-login me-2"></i> Initialize Session
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center text-muted mt-3">
                &copy; {{ date('Y') }} Electron Core Systems
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" defer></script>
    <script>
        document.getElementById('toggle-password').addEventListener('click', function(e) {
            e.preventDefault();
            var input = document.getElementById('password');
            var icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ti ti-eye-off';
            } else {
                input.type = 'password';
                icon.className = 'ti ti-eye';
            }
        });
    </script>
</body>
</html>
