<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user(); // kasir yang login
        return view('role.kasir.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'phone'    => ['nullable','string','max:25'],
            'password' => ['nullable','min:5','confirmed'],
        ]);

        // Kalau password tidak diisi, jangan update kolom password
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data); // model User-mu sudah auto-hash password (sesuai komentar di register)

        return back()->with('ok', 'Profil berhasil diperbarui.');
    }
}
