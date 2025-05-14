@extends('customer.layouts.master')

@section('page-title', 'Edit Profil Saya')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Profil</li>
@endsection

@section('content')
    <div class="page-heading mb-4">
        <h3>Edit Profil Saya</h3>
        <p class="text-subtitle text-muted">Perbarui informasi pribadi dan keamanan akun Anda.</p>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-md-8 col-lg-7 col-xl-6"> {{-- Batasi lebar form --}}
                @include('admin.partials.alerts') {{-- Atau partial alert customer --}}

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="card-title">Informasi Akun</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('customer.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT') {{-- Method untuk update --}}

                            {{-- Informasi Pribadi --}}
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Alamat Email</label>
                                <input type="email" class="form-control bg-light" id="email" name="email_display"
                                    value="{{ $customer->email }}" readonly disabled>
                                <small class="form-text text-muted">Email tidak dapat diubah.</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="phone_number" class="form-label">Nomor Telepon <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                    id="phone_number" name="phone_number"
                                    value="{{ old('phone_number', $customer->phone_number) }}" required>
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="address" class="form-label">Alamat Lengkap <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                    required>{{ old('address', $customer->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="gender" class="form-label">Jenis Kelamin</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender"
                                    name="gender">
                                    <option value="">Pilih Jenis Kelamin...</option>
                                    @foreach ($genders as $key => $value)
                                        <option value="{{ $key }}"
                                            {{ old('gender', $customer->gender) == $key ? 'selected' : '' }}>
                                            {{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">
                            <h5 class="mb-3">Ubah Password (Opsional)</h5>

                            <div class="form-group mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" autocomplete="current-password">
                                <small class="form-text text-muted">Isi jika ingin mengubah password.</small>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                    id="new_password" name="new_password" autocomplete="new-password">
                                <small class="form-text text-muted">Minimal 8 karakter, kombinasi huruf besar, kecil, &
                                    angka.</small>
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="new_password_confirmation"
                                    name="new_password_confirmation">
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan Profil</button>
                                <a href="{{ route('customer.dashboard') }}" class="btn btn-light ms-2">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
