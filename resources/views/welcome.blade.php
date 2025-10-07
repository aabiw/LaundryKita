@extends('layouts.app')

@section('title', 'LaundryKita - Digital Laundry Management')

@section('content')
<!-- Hero Section -->
<section class="hero-section d-flex align-items-center">
    <div class="container text-center position-relative">
        <div class="hero-content">
            <!-- Logo -->
            <img src="{{ asset('images/no_background.png')}}" alt="Logo Laundry" class="hero-logo">
        </div>
        <h1 class="title-hero">
            DIGITAL LAUNDRY <br> MANAGEMENT
        </h1>

        <p class="subtitle-hero">
            Nikmati kemudahan laundry digital dengan atur layanan, lacak pesanan, dan ambil cucian.
        </p>

        <p class="desc-hero">
            Sebagai platform laundry digital, kami menghadirkan layanan modern yang mengutamakan 
            kecepatan, kualitas, dan kenyamanan. Mulai dari drop-off hingga cucian siap diambil, 
            semua dapat dipantau secara real-time melalui sistem kami. Laundry jadi lebih teratur, 
            efisien, dan bebas khawatir.
        </p>

        <a href="{{ route('login') }}" class="btn btn-accent btn-lg">
            MASUK
        </a>

        <!-- gelembung CSS -->
        <div class="bubble" style="top: 15%; left: 10%; width: 80px; height: 80px;"></div>
        <div class="bubble" style="top: 40%; right: 15%; width: 120px; height: 120px;"></div>
        <div class="bubble" style="bottom: 20%; left: 30%; width: 60px; height: 60px;"></div>
        
        <!-- gelembung PNG -->
        <img src="{{ asset('images/4.png') }}" class="gelembung-png" alt="Gelembung">

        <!-- icon pojok kanan bawah -->
        <img src="{{ asset('images/9.png') }}" class="icon-bottom" alt="Icon Laundry">
    </div>
</section>

<!-- Footer -->
<footer class="py-4 bg-light">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="text-muted">© @KelompokA.Basdat 2025</span>
    </div>
</footer>
@endsection

@push('styles')
<style>
/* Hero Section */
.hero-section {
    min-height: calc(100vh - 80px); /* Disesuaikan tanpa navbar, hanya kurangi tinggi footer */
    position: relative;
    overflow: hidden;
    background: #f1f5f4ff; /* Sesuai background body di layout */
}

/* Title */
.title-hero {
    font-family: 'Intro Rust', sans-serif;
    font-size: 60.8px;
    font-weight: bold;
    color: #0a3d62; /* Sesuai warna headings di layout */
    text-shadow: 2px 2px 5px rgba(0,0,0,0.1); /* Shadow ringan untuk konsistensi */
    margin-bottom: 20px;
    z-index: 1;
    position: relative;
}

/* Subtitle */
.subtitle-hero {
    font-family: 'TT Norms', sans-serif;
    font-size: 19px;
    color: #6c757d; /* text-muted seperti Bootstrap, disesuaikan dengan layout */
    font-weight: 500;
    margin-bottom: 20px;
    z-index: 1;
    position: relative;
}

/* Description */
.desc-hero {
    font-family: 'Public Sans', sans-serif;
    font-size: 14.4px;
    color: #6c757d; /* text-muted/secondary seperti layout */
    max-width: 700px;
    margin: 0 auto 30px auto;
    z-index: 1;
    position: relative;
}

/* Button - Menggunakan class btn-accent dari layout */
.btn-accent {
    font-family: 'Intro Rust', sans-serif;
    padding: 12px 40px;
    border-radius: 25px;
    font-size: 18px;
    text-decoration: none;
    transition: 0.3s;
    z-index: 1;
    position: relative;
    font-weight: bold;
    text-transform: uppercase;
}
.btn-accent:hover {
    color: #fff; /* Pastikan hover tetap white text seperti layout */
}

/* Gelembung CSS - Disesuaikan dengan warna accent layout (hijau/teal transparan) */
.bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(164, 207, 74, 0.2); /* rgba dari #A4CF4A dengan opacity */
    z-index: 0;
    animation: floatBubble 12s infinite ease-in-out;
}

@keyframes floatBubble {
    0% { transform: translateY(0); opacity: 0.6; }
    50% { transform: translateY(-40px); opacity: 0.9; }
    100% { transform: translateY(0); opacity: 0.6; }
}

/* Gelembung PNG */
.gelembung-png {
    position: absolute;
    bottom: 200px;
    left: 10%;
    width: 300px;
    z-index: 0;
    opacity: 0.8;
}

.hero-logo {
    width: 150px;   /* atur ukuran logo */
    margin-bottom: 0px;
}

/* Icon pojok kanan bawah */
.icon-bottom {
    position: absolute;
    bottom: -50px;
    right: 0px;
    width: 250px;
    z-index: 0;
    opacity: 0.8;
}
</style>
@endpush