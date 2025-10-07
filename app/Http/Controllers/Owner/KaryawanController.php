<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // ← untuk generate password acak

class KaryawanController extends Controller
{
    public function index()
    {
        $kasir = User::where('role','kasir')
            ->orderBy('name')
            ->get(['id','name','email']);

        return view('role.pemilik.karyawan', [
            'kasir'       => $kasir,
            'totalKasir'  => $kasir->count(),
            'semuaAktif'  => true,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'nullable|min:5',
        ]);

        // password sementara: pakai input jika diisi, jika kosong generate acak
        $tempPassword = $data['password'] ?? (
            method_exists(Str::class, 'password') ? Str::password(10) : Str::random(10)
        );

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $tempPassword, // diasumsikan di-hash oleh mutator di model User
            'role'     => 'kasir',
        ]);

        // tampilkan sekali ke pemilik
        return back()->with([
            'ok'         => 'Kasir berhasil ditambahkan.',
            'temp_email' => $user->email,
            'temp_pass'  => $tempPassword,
        ]);
    }

    public function update(Request $r, User $user)
    {
        abort_unless($user->role === 'kasir', 403);

        $data = $r->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:5',
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = $data['password']; // mutator hash
        }
        $user->save();

        return back()->with('ok','Kasir berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_unless($user->role === 'kasir', 403);
        $user->delete();
        return back()->with('ok','Kasir dihapus.');
    }

    // === Tambahan: reset password kasir ===
    public function resetPassword(Request $r, User $user)
    {
        abort_unless($user->role === 'kasir', 403);

        $newPass = method_exists(Str::class, 'password') ? Str::password(10) : Str::random(10);
        $user->password = $newPass; // mutator hash
        $user->save();

        return back()->with([
            'ok'         => 'Password kasir telah direset.',
            'temp_email' => $user->email,
            'temp_pass'  => $newPass,
        ]);
    }
}
