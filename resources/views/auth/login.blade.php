<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ERP Bio Pilar') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Core CSS (Bootstrap) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">

    <!-- FOUC Prevention Script -->
    <script>
        (function() {
            var currentTheme = localStorage.getItem('theme');
            if (!currentTheme) {
                currentTheme = 'light';
            }
            document.documentElement.setAttribute('data-theme', currentTheme);
        })();
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        .login-left {
            background-image: url('{{ asset("login.jpeg") }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .login-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        .login-content {
            position: relative;
            z-index: 2;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            border-color: #7052ff;
            box-shadow: 0 0 0 0.25rem rgba(112, 82, 255, 0.25);
        }
        .btn-custom {
            background-color: #7052ff;
            border-color: #7052ff;
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .btn-custom:hover {
            background-color: #5c40eb;
            border-color: #5c40eb;
            color: #fff;
        }
        .text-custom {
            color: #7052ff;
        }
        .text-custom:hover {
            color: #5c40eb;
        }
        /* Toggle Switch */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }
        .form-switch .form-check-input:checked {
            background-color: #7052ff;
            border-color: #7052ff;
        }
    </style>
</head>
<body>
    <div class="row g-0 vh-100">
        <!-- Left Side: Image -->
        <div class="col-lg-6 d-none d-lg-flex login-left flex-column justify-content-between p-5 text-white">
            <div class="login-overlay"></div>
            
            <!-- Logo & Brand -->
            <div class="login-content d-flex align-items-center gap-2">
                <img src="{{ asset('logo11.png') }}" alt="ERP Bio Pilar Logo" width="90" height="80">
                <h3 class="m-0 fw-bold text-white">ERP Bio Pilar</h3>
            </div>
            
            <!-- Quote -->
            <div class="login-content mb-5">
                <h1 class="fw-bold mb-4 text-white" style="font-size: 2.5rem; line-height: 1.3;">"Simply all the tools that<br>my team and I need."</h1>
                <p class="mb-0 fw-semibold fs-5 text-white">Dale Carnegie</p>
                <p class="text-light" style="opacity: 0.8;">ERP Digitalization Intern</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="col-lg-6 d-flex flex-column align-items-center justify-content-center p-4 p-sm-5">
            
            <!-- Mobile Logo -->
            <div class="d-lg-none d-flex align-items-center gap-2 mb-4">
                <img src="{{ asset('logo11.png') }}" alt="ERP Bio Pilar Logo" width="28" height="28">
                <h3 class="m-0 fw-bold">ERP Bio Pilar</h3>
            </div>

            <div style="width: 100%; max-width: 420px;">
                <div class="text-center mb-5">
                    <h2 class="fw-bold  mb-2">Welcome Back to ERP</h2>
                    <p class="text-muted">Manage your business operations efficiently and seamlessly.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- Email -->
                    <div class="mb-4">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" 
                            class="form-control" placeholder="Email" />
                        @error('email')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-2">
                        <div class="position-relative">
                            <input id="password" type="password" name="password" required autocomplete="current-password" 
                                class="form-control pe-5" placeholder="Password" />
                            <span class="position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer; z-index: 10;" id="togglePassword">
                                <i class="bi bi-eye text-muted" id="toggleIcon"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="text-danger mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Forgot Password -->
                    <div class="d-flex justify-content-start mb-4">
                        <a href="{{ route('password.request') }}" class="text-custom text-decoration-none small fw-bold">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Remember Me -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
                        <label class="form-check-label text-muted small fw-semibold" for="remember_me">
                            Remember sign in details
                        </label>
                        <div class="form-check form-switch m-0 p-0">
                            <input class="form-check-input m-0 float-end" type="checkbox" role="switch" id="remember_me" name="remember">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-custom w-100 shadow-sm mb-4">
                        Log in
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    document.getElementById('togglePassword').addEventListener('click', function (e) {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    });
</script>
</html>
