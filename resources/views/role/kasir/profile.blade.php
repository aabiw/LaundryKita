@extends('layouts.app')

@section('title', 'Profil Kasir - LaundryKita')

@section('content')
<div class="d-flex">
    {{-- Sidebar (shared) --}}
    @include('role.kasir.partials.sidebar')

    <!-- Main -->
    <div class="flex-grow-1 p-4" style="background:#f1f5f4ff;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Profil Saya</h2>
                <p class="text-muted mb-0">Lengkapi data diri kamu sebagai kasir.</p>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('kasir.profil.update') }}">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="kasir" disabled>
                            <div class="form-text">Role ditetapkan oleh sistem.</div>
                        </div>

                        <div class="col-12"><hr></div>

                        <div class="col-md-6">
                            <label class="form-label">Password Baru <span class="text-muted">(opsional)</span></label>
                            <div class="position-relative">
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       class="form-control pe-5" 
                                       placeholder="Kosongkan jika tidak ganti">
                                <span class="position-absolute top-50 end-0 translate-middle-y pe-3 cursor-pointer" 
                                      onclick="togglePassword('password')">
                                    <i class="bi bi-eye text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <div class="position-relative">
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       class="form-control pe-5" 
                                       placeholder="Ulangi password baru">
                                <span class="position-absolute top-50 end-0 translate-middle-y pe-3 cursor-pointer" 
                                      onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye text-muted"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-accent" type="submit">Simpan</button>
                        <a href="{{ route('kasir.dashboard') }}" class="btn btn-secondary">Batalkan</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .text-accent { color:#35C8B4 !important; }
    .bg-accent { background-color:#35C8B4 !important; }
    .btn-accent { background:#35C8B4; color:#fff; border-radius:25px; padding:8px 20px; font-weight:500; border:none; }
    .btn-accent:hover { background:#2ba497; color:#fff; }
    .cursor-pointer { cursor: pointer; }
</style>
@endpush

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endpush