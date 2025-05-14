<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rental Outdoor</title> {{-- Judul disesuaikan --}}

    {{-- Sesuaikan path jika struktur asset Anda berbeda --}}
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/auth.css') }}">
    <style>
        /* Pastikan background auth-right muncul */
         #auth-right {
              background: url("{{ asset('assets/static/images/bg-auth.jpg') }}") center center; /* Ganti path jika perlu */
              background-size: cover;
          }
      </style>
</head>

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo mb-4 text-center">
                        {{-- Link logo ke halaman login customer --}}
                        <a href="{{ route('customer.login') }}">
                            <img src="{{ asset('assets/compiled/png/logoapp.png') }}" alt="Logo"
                                style="width: 150px; height: auto;">
                        </a>
                    </div>
                    <h1 class="auth-title">Log in.</h1>
                    <p class="auth-subtitle mb-5">Masuk dengan akun rental Anda.</p> {{-- Teks disesuaikan --}}

                    {{-- Include alert partial dari admin (atau buat partial khusus auth) --}}
                    @include('customer.partials.alerts')

                    {{-- Form action ke route login customer --}}
                    <form action="{{ route('customer.login.post') }}" method="POST">
                        @csrf
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="email" name="email" class="form-control form-control-xl @error('email') is-invalid @enderror"
                                placeholder="Email" value="{{ old('email') }}" required autofocus>
                            <div class="form-control-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            {{-- Pesan error spesifik untuk email dari ValidationException --}}
                            @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" name="password" class="form-control form-control-xl @error('password') is-invalid @enderror"
                                placeholder="Password" required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                             @error('password')
                             <span class="invalid-feedback d-block" role="alert">
                                 <strong>{{ $message }}</strong>
                             </span>
                             @enderror
                        </div>
                        <div class="form-check form-check-lg d-flex align-items-end">
                            <input class="form-check-input me-2" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-gray-600" for="remember">
                                Ingat saya
                            </label>
                        </div>
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5" type="submit">Log in</button>
                    </form>

                    <div class="text-center mt-5 text-lg fs-4">
                         {{-- Link ke halaman registrasi customer --}}
                        <p class="text-gray-600">Belum punya akun? <a href="{{ route('customer.register') }}" class="font-bold">Daftar sekarang</a>.</p>
                        {{-- Link ke lupa password customer (jika ada) --}}
                        {{-- <p><a class="font-bold" href="{{ route('customer.password.request') }}">Lupa password?</a></p> --}}
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right">
                </div>
            </div>
        </div>
    </div>
    {{-- Bootstrap JS untuk alert dismiss (jika perlu) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>