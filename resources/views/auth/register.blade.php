@extends('layouts.app')

@section('title', 'Register - LaundryKita')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #f9f9f9;">
    <div class="auth-card text-center">
        <h4 class="brand-logo mb-4">LAUNDRYKITA</h4>
        <h5 class="fw-bold">Buat akun</h5>
        <p class="text-muted mb-4"> Gabung LaundryKita hari ini</p>

        @if(session('error'))
          <div class="alert alert-danger text-start">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
          <div class="alert alert-danger text-start">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <div class="mb-3 text-start">
                <label class="form-label">Nama lengkap</label>
                <input type="text" class="form-control" name="name" placeholder="Masukan nama lengkap" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="Masukan email" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Nomor HP</label>
                <input type="text" class="form-control" name="phone" placeholder="Masukan nomor HP" value="{{ old('phone') }}">
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Kata sandi</label>
                <input type="password" class="form-control" name="password" placeholder="Buat katasandi" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label">Konfirmasi katasandi</label>
                <input type="password" class="form-control" name="password_confirmation" placeholder="Masukan katasandi" required>
            </div>

            {{-- default pelanggan --}}
            <input type="hidden" name="role" value="user">

            {{-- daftar sebagai pemilik pakai kode (opsional) --}}
            <div class="mb-3 text-start">
                <label class="form-label">Kode Pemilik (opsional)</label>
                <input type="text" class="form-control" name="owner_code" placeholder="Masukkan kode jika mendaftar sebagai pemilik">
                <small class="text-muted">Kosongkan jika mendaftar sebagai pelanggan.</small>
            </div>

            <div class="form-check mb-3 text-start">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label for="terms" class="form-check-label">Saya setuju dengan Syarat dan Ketentuan</label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-dark">Buat akun</button>
            </div>
        </form>

        <p class="mt-3">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none">Masuk</a></p>
        <p class="mt-4"><a href="{{ url('/') }}" class="text-muted text-decoration-none">&larr; Kembali </a></p>
    </div>
</div>
@endsection
