<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()    { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $cred['email'])->first();

        if (!$user || !Hash::check($cred['password'], $user->password)) {
            return back()->with('error', 'Email atau password salah!');
        }

        // simpan ke session (dipakai middleware role.session)
        $request->session()->put('role', $user->role);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_name', $user->name);

        return match ($user->role) {
            'pemilik' => redirect('/pemilik'),
            'kasir'   => redirect('/kasir'),
            default   => redirect('/user'),
        };
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required','string','max:100'],
            'email'                 => ['required','email','unique:users,email'],
            'password'              => ['required','min:5','confirmed'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // model User kamu sudah casts 'password' => 'hashed'
            'role'     => 'user',            // default pelanggan
        ]);

        $request->session()->put('role', $user->role);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_name', $user->name);

        return redirect('/user');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['role','user_id','user_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
