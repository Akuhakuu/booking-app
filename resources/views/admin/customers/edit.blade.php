@extends('admin.layouts.master')

@section('page-title', 'Edit Customer: ' . $customer->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Kelola Customers</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Customer</li>
@endsection

@section('content')
<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Form Edit Customer: {{ $customer->name }}</h4>
                </div>
                <div class="card-body">
                    @include('admin.partials.alerts')

                    <form action="{{ route('admin.customers.update', $customer->hashid) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Kolom Kiri --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $customer->name) }}" required autofocus>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $customer->email) }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                     <label for="phone_number" class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
                                     <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                         id="phone_number" name="phone_number" value="{{ old('phone_number', $customer->phone_number) }}" required>
                                     @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                 </div>

                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">Password Baru (Opsional)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" autocomplete="new-password">
                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password. Jika diisi, minimal 8 karakter, mengandung huruf besar, kecil, dan angka.</small>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                    <small class="form-text text-muted">Wajib diisi jika mengisi password baru.</small>
                                </div>

                            </div> {{-- End Kolom Kiri --}}

                            {{-- Kolom Kanan --}}
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="4" required>{{ old('address', $customer->address) }}</textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="gender" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                        <option value="">Pilih Jenis Kelamin...</option>
                                        @foreach($genders as $key => $value)
                                            <option value="{{ $key }}" {{ old('gender', $customer->gender) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                     @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Status Akun <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                         @foreach($statuses as $key => $value)
                                             <option value="{{ $key }}" {{ old('status', $customer->status) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                         @endforeach
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div> {{-- End Kolom Kanan --}}
                        </div> {{-- End Row --}}

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Update Customer</button>
                            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection