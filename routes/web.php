<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\KaryawanController;
use App\Http\Controllers\LayananController;
// use App\Http\Controllers\Owner\AnalyticsController; // [UTS HOLD] sementara tidak dipakai
use App\Http\Controllers\Owner\HistoryController; // history pemilik
use App\Http\Controllers\Kasir\KasirHistoryController;

Route::get('/', fn () => view('welcome'));

Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/register', fn () => view('auth.register'))->name('register');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// LOGIN via DB - DIMODIFIKASI: Tambah dummy untuk pemilik, sisanya sama
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required','email'],
        'password' => ['required'],
    ]);

    // DUMMY UNTUK PEMILIK (hardcoded untuk testing)
    $dummyEmail = 'admin@laundrykita.com';
    $dummyPassword = 'admin123';
    if ($credentials['email'] === $dummyEmail && $credentials['password'] === $dummyPassword) {
        // Cari atau buat user dummy di DB (hanya untuk testing)
        $user = User::where('email', $dummyEmail)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Pemilik',
                'email' => $dummyEmail,
                'password' => bcrypt($dummyPassword), // Hash otomatis
                'role' => 'pemilik',
                'phone' => null, // Opsional
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        return redirect('/pemilik'); // Redirect ke dashboard pemilik
    }

    // UNTUK ROLE LAIN (kasir/user): Lanjut ke auth normal seperti code asli
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        $role = Auth::user()->role;
        return $role === 'pemilik' ? redirect('/pemilik')
             : ($role === 'kasir' ? redirect('/kasir') : redirect('/user'));
    }

    // Error untuk semua kasus (tambah hint dummy untuk pemilik)
    return back()->with('error', 'Email atau password salah!');
})->name('login.post');

// REGISTER - TETAP SAMA (bisa buat pemilik via owner_code, kasir via pemilik login, user normal)
Route::post('/register', function (Request $r) {
    $data = $r->validate([
        'name'       => ['required','string','max:100'],
        'email'      => ['required','email','unique:users,email'],
        'phone'      => ['nullable','string','max:25'],
        'password'   => ['required','min:5','confirmed'],
        'role'       => ['nullable', Rule::in(['user','kasir','pemilik'])],
        'owner_code' => ['nullable','string'],
    ]);

    $role = 'user';

    if (Auth::check() && Auth::user()->role === 'pemilik' && ($data['role'] ?? null) === 'kasir') {
        $role = 'kasir';
    }

    $invite = env('OWNER_INVITE_CODE');
    if (!empty($data['owner_code'])) {
        if ($invite && hash_equals($invite, $data['owner_code'])) {
            $role = 'pemilik';
        } else {
            return back()->withInput()->with('error','Kode pemilik salah.');
        }
    }

    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'phone'    => $data['phone'] ?? null,
        'password' => $data['password'], // auto-hash di model
        'role'     => $role,
    ]);

    if (Auth::check() && Auth::user()->role === 'pemilik' && $role === 'kasir') {
        return redirect()->route('pemilik.karyawan')->with('ok','Kasir berhasil didaftarkan.');
    }

    Auth::login($user);
    return $role === 'pemilik' ? redirect('/pemilik')
         : ($role === 'kasir' ? redirect('/kasir') : redirect('/user'));
})->name('register.post');


// =======================
// AREA KASIR
// =======================
use App\Http\Controllers\Kasir\DashboardController as KasirDashboardController;
use App\Http\Controllers\Kasir\PesananController;
use App\Http\Controllers\Kasir\PembayaranController;
use App\Http\Controllers\Kasir\ProfileController; // controller profil kasir

Route::prefix('kasir')->middleware('role.session:kasir')->name('kasir.')->group(function () {
    // Dashboard
    Route::get('/', [KasirDashboardController::class, 'index'])->name('dashboard');

    // Pesanan
    Route::get('/pesanan-aktif', [PesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan-baru', [PesananController::class, 'create'])->name('pesanan.create');
    Route::post('/pesanan-baru', [PesananController::class, 'store'])->name('pesanan.store');

    // Penting: ini rute PATCH yang dipakai Blade → route('kasir.pesanan.updateStatus', $order->id)
    Route::patch('/pesanan/{pesanan}/status', [PesananController::class, 'updateStatus'])->name('pesanan.updateStatus');

    Route::delete('/pesanan/{pesanan}', [PesananController::class, 'destroy'])->name('pesanan.destroy');

    // >>> Tambahan: Cetak Tag untuk kasir
    Route::get('/pesanan/{pesanan}/tag', [PesananController::class, 'cetakTag'])->name('pesanan.cetakTag');
    // <<<

    // Pembayaran / Status Pembayaran
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran/{id}/proses', [PembayaranController::class, 'proses'])->name('pembayaran.proses');
    // NEW: tandai LUNAS dari halaman pembayaran
    Route::patch('/pembayaran/{id}/lunas', [PembayaranController::class, 'markPaid'])->name('pembayaran.markPaid');

    // (opsional) Layanan untuk kasir
    Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
    Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store');

    // Profil Kasir (EDIT + UPDATE)
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

    // History
    Route::get('/history',           [KasirHistoryController::class, 'index'])->name('history');
    Route::get('/history/{pesanan}', [KasirHistoryController::class, 'show'])->name('history.show');
});


// =======================
// AREA PEMILIK - TETAP SAMA
// =======================
Route::prefix('pemilik')->middleware('role.session:pemilik')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('pemilik.dashboard');

    // Karyawan
    Route::get('/karyawan',           [KaryawanController::class, 'index'])->name('pemilik.karyawan');
    Route::post('/karyawan',          [KaryawanController::class, 'store'])->name('pemilik.karyawan.store');
    Route::put('/karyawan/{user}',    [KaryawanController::class, 'update'])->name('pemilik.karyawan.update');
    Route::delete('/karyawan/{user}', [KaryawanController::class, 'destroy'])->name('pemilik.karyawan.destroy');
    Route::post('/karyawan/{user}/reset-password', [KaryawanController::class, 'resetPassword'])
        ->name('pemilik.karyawan.reset');

    // Layanan
    Route::get('/layanan',         [LayananController::class, 'index'])->name('pemilik.layanan.index');
    Route::post('/layanan',        [LayananController::class, 'store'])->name('pemilik.layanan.store');
    Route::put('/layanan/{id}',    [LayananController::class, 'update'])->name('pemilik.layanan.update');
    Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])->name('pemilik.layanan.destroy');
    Route::get('/layanan/history', [LayananController::class, 'history'])->name('pemilik.layanan.history');

    // ===== History Pesanan (read-only) + fitur tambahan
    Route::get('/history',                 [HistoryController::class, 'index'])->name('pemilik.history');
    Route::get('/history/export',          [HistoryController::class, 'exportCsv'])->name('pemilik.history.export');   // CSV sesuai filter
    Route::get('/history/{pesanan}',       [HistoryController::class, 'show'])->name('pemilik.history.show');          // JSON detail+log (modal)
    Route::get('/history/{pesanan}/struk', [HistoryController::class, 'printReceipt'])->name('pemilik.history.struk'); // cetak struk
    Route::get('/history/{pesanan}/tag',   [HistoryController::class, 'printTag'])->name('pemilik.history.tag');       // cetak tag/claim

    // ===== Analitik (dinonaktifkan sementara sampai setelah UTS)
    // [UTS HOLD] Nonaktifkan rute Analitik sementara
    // Route::get('/analitik',      [AnalyticsController::class, 'index'])->name('pemilik.analitik');
    // Route::get('/analitik/data', [AnalyticsController::class, 'data'])->name('pemilik.analitik.data');
});

// ----- Halaman user (placeholder) - TETAP SAMA
//Route::get('/user', fn () => view('role.user.user'))->middleware('role.session:user');
