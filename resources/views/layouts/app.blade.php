<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LaundryKita')</title>

    <!-- Favicon (logo di tab browser) -->
    <link rel="icon" href="{{ asset('images/no_background.png') }}" type="image/png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f1f5f4ff;
            margin: 0;
            padding: 0;
        }

        /* Tombol warna utama */
        .btn-accent {
            background: #A4CF4A;
            color: #fff;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: bold;
            text-transform: uppercase;
            border: none;
        }
        .btn-accent:hover {
            background: #35C8B4;
            color: #fff;
        }

        /* Card untuk form login/register */
        .auth-card {
            max-width: 400px;
            width: 100%;
            border-radius: 15px;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Logo dan brand */
        .brand-logo {
            font-weight: bold;
            color: #35C8B4;
            font-size: 1.3rem;
            letter-spacing: 1px;
        }

        /* Judul section */
        h1, h2, h3, h4, h5 {
            color: #0a3d62;
        }

        /* Gambar responsif */
        .illustration {
            max-width: 500px;
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Konten utama --}}
    @yield('content')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
    
</body>
</html>