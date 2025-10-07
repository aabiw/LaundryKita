@extends('layouts.app')

@section('title', 'Login - LaundryKita')

@section('content')

<div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #f1f5f4ff; position: relative; overflow: hidden;">

    <!-- Gelembung sabun - Disesuaikan warna dengan accent layout (teal transparan) -->
    <div class="bubble" style="width: 80px; height: 80px; top: 10%; left: 15%;"></div>
    <div class="bubble" style="width: 100px; height: 100px; top: 30%; right: 20%;"></div>
    <div class="bubble" style="width: 60px; height: 60px; bottom: 15%; left: 25%;"></div>

    <!-- Ilustrasi pojok kanan bawah -->
    <img src="{{ asset('images/3.png') }}" 
         alt="Laundry Animation" 
         style="position: absolute; bottom: 0; right: 0; max-width: 450px;">

    <div class="auth-card text-center position-relative" style="z-index: 2; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 400px; width: 100%;">

        <!-- Logo -->
        <img src="{{ asset('images/LAUNDRYKITA.png') }}" alt="LaundryKita Logo" class="brand-logo mb-2" style="max-width: 100px;">

        <h5 class="fw-bold mt-2" style="color: #0a3d62;">Selamat datang</h5>
        <p class="text-muted mb-4">Masuk ke akun LaundryKita Anda</p>

        <!-- Pesan error -->
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3 text-start">
                <label class="form-label" style="color: #0a3d62;">Email</label>
                <input type="email" class="form-control" name="email" placeholder="Masukan email" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label" style="color: #0a3d62;">Password</label>
                <div class="input-group">
                    <input type="password" id="password" class="form-control" name="password" placeholder="Masukan password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" tabindex="-1">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-accent">Masuk</button>
            </div>
        </form>
<!--
        <p class="mt-3">
            Apakah kamu tidak punya akun?
            <a href="{{ route('register') }}" class="text-decoration-none" style="color: #35C8B4;">Daftar</a>
        </p>
-->
        <!-- Tombol Kembali -->
        <p class="mt-4">
            <a href="{{ url('/') }}" class="text-muted text-decoration-none">&larr; Kembali</a>
        </p>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Gelembung - Disesuaikan dengan warna accent layout (teal transparan dari #35C8B4) */
    .bubble {
        position: absolute;
        border-radius: 50%;
        background: rgba(53, 200, 180, 0.2); /* rgba dari #35C8B4 dengan opacity */
        animation: float 10s infinite ease-in-out;
    }

    @keyframes float {
        0% { transform: translateY(0); }
        50% { transform: translateY(-30px); }
        100% { transform: translateY(0); }
    }

    /* Pastikan btn-accent sesuai layout (sudah ada di layout, tapi override jika perlu untuk konsistensi) */
    .btn-accent {
        background: #A4CF4A;
        color: #fff;
        border-radius: 25px;
        padding: 10px 25px;
        font-weight: bold;
        text-transform: uppercase;
        border: none;
        transition: 0.3s;
    }
    .btn-accent:hover {
        background: #35C8B4;
        color: #fff;
    }
</style>
@endpush

@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.classList.replace("bi-eye", "bi-eye-slash");
    } else {
        passwordInput.type = "password";
        eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
    }
}
</script>
@endpush