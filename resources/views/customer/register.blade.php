<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Rental Outdoor</title>

    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/auth.css') }}">
    <style>
        #auth-right {
            background: url("{{ asset('assets/static/images/bg-auth.jpg') }}") center center;
            background-size: cover;
        }

        /* Style untuk tombol toggle password */
        .password-toggle-icon {
            cursor: pointer;
            /* Tambahkan z-index agar di atas input jika perlu */
        }

        /* Style untuk pesan kesamaan password */
        .password-match-message {
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
        .password-match {
            color: green;
        }
        .password-mismatch {
            color: red;
        }
    </style>
</head>

<body>
    <script src="{{ asset('assets/static/js/initTheme.js') }}"></script>
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-6 col-12">
                <div id="auth-left">
                    <div class="auth-logo mb-4 text-center">
                        <a href="{{ route('customer.login') }}">
                            <img src="{{ asset('assets/compiled/png/logoapp.png') }}" alt="Logo"
                                style="width: 150px; height: auto;">
                        </a>
                    </div>
                    <h1 class="auth-title">Sign Up</h1>
                    <p class="auth-subtitle mb-4">Masukkan data Anda untuk mendaftar.</p>

                    @include('customer.partials.alerts')

                    <form action="{{ route('customer.register.post') }}" method="POST">
                        @csrf
                        <div class="form-group position-relative has-icon-left mb-3">
                            <input type="text"
                                class="form-control form-control-xl @error('name') is-invalid @enderror"
                                placeholder="Nama Lengkap" name="name" value="{{ old('name') }}" required>
                            <div class="form-control-icon"><i class="bi bi-person"></i></div>
                            @error('name')
                                <span class="invalid-feedback d-block"
                                    role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group position-relative has-icon-left mb-3">
                            <input type="email"
                                class="form-control form-control-xl @error('email') is-invalid @enderror"
                                placeholder="Email Aktif" name="email" value="{{ old('email') }}" required>
                            <div class="form-control-icon"><i class="bi bi-envelope"></i></div>
                            @error('email')
                                <span class="invalid-feedback d-block"
                                    role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group position-relative has-icon-left mb-3">
                            <input type="text"
                                class="form-control form-control-xl @error('phone_number') is-invalid @enderror"
                                placeholder="Nomor Telepon" name="phone_number" value="{{ old('phone_number') }}"
                                required>
                            <div class="form-control-icon"><i class="bi bi-phone"></i></div>
                            @error('phone_number')
                                <span class="invalid-feedback d-block"
                                    role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group position-relative has-icon-left mb-3">
                            <textarea class="form-control form-control-xl @error('address') is-invalid @enderror" placeholder="Alamat Lengkap"
                                name="address" rows="3" required>{{ old('address') }}</textarea>
                            @error('address')
                                <span class="invalid-feedback d-block"
                                    role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group position-relative mb-3"> {{-- Icon tidak perlu untuk select --}}
                            <select class="form-select form-select-xl @error('gender') is-invalid @enderror"
                                name="gender">
                                <option value="" selected>Pilih Jenis Kelamin</option>
                                @foreach ($genders as $key => $value)
                                    <option value="{{ $key }}" {{ old('gender') == $key ? 'selected' : '' }}>
                                        {{ $value }}</option>
                                @endforeach
                            </select>
                            @error('gender')
                                <span class="invalid-feedback d-block"
                                    role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Kolom Password dengan Toggle --}}
                        <div class="form-group position-relative has-icon-left mb-3">
                            <input type="password"
                                class="form-control form-control-xl @error('password') is-invalid @enderror"
                                placeholder="Password" name="password" id="password" required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div class="form-control-icon password-toggle-icon" style="right: 0; left: auto; top: 50%; transform: translateY(-50%); padding-right: 1rem;" onclick="togglePasswordVisibility('password', 'toggle-icon-password')">
                                <i class="bi bi-eye-fill" id="toggle-icon-password"></i>
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block"
                                    role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        {{-- Kolom Konfirmasi Password dengan Toggle dan Pesan Kesamaan --}}
                        <div class="form-group position-relative has-icon-left mb-2"> {{-- Kurangi mb sedikit --}}
                            <input type="password" class="form-control form-control-xl"
                                placeholder="Konfirmasi Password" name="password_confirmation" id="password_confirmation" required>
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div class="form-control-icon password-toggle-icon" style="right: 0; left: auto; top: 50%; transform: translateY(-50%); padding-right: 1rem;" onclick="togglePasswordVisibility('password_confirmation', 'toggle-icon-confirm-password')">
                                <i class="bi bi-eye-fill" id="toggle-icon-confirm-password"></i>
                            </div>
                        </div>
                        <div id="password-match-message-container" class="mb-4">
                             {{-- Pesan kesamaan password akan muncul di sini --}}
                        </div>


                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-4" type="submit">Sign Up</button>
                    </form>

                    <div class="text-center mt-5 text-lg fs-4">
                        <p class="text-gray-600">Sudah punya akun? <a href="{{ route('customer.login') }}"
                                class="font-bold">Log in</a>.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div id="auth-right">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(iconId);
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove("bi-eye-fill");
                toggleIcon.classList.add("bi-eye-slash-fill");
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove("bi-eye-slash-fill");
                toggleIcon.classList.add("bi-eye-fill");
            }
        }

        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const messageContainer = document.getElementById('password-match-message-container');

        function checkPasswordMatch() {
            // Hapus pesan sebelumnya
            messageContainer.innerHTML = '';

            if (confirmPasswordInput.value.length > 0) { // Hanya tampilkan jika konfirmasi diisi
                const messageElement = document.createElement('div');
                messageElement.classList.add('password-match-message');

                if (passwordInput.value === confirmPasswordInput.value) {
                    if (passwordInput.value.length > 0) { // Pastikan password utama juga diisi
                        messageElement.textContent = '✓ Password cocok!';
                        messageElement.classList.add('password-match');
                        messageElement.classList.remove('password-mismatch');
                    }
                } else {
                    messageElement.textContent = '✗ Password tidak cocok!';
                    messageElement.classList.add('password-mismatch');
                    messageElement.classList.remove('password-match');
                }
                 messageContainer.appendChild(messageElement);
            }
        }

        if (passwordInput && confirmPasswordInput && messageContainer) {
            passwordInput.addEventListener('input', checkPasswordMatch);
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }
    </script>
</body>
</html>
