<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $role = Auth::check() ? Auth::user()->role : session('role');
        if (!$role || !in_array($role, $roles, true)) {
            return redirect()->route('login')->with('error','Akses ditolak.');
        }
        return $next($request);
    }
}
