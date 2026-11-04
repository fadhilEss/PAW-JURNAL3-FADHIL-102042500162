@extends('layouts.app')

@section('title', 'Daftar Akun Mahasiswa Baru — CeLOE LMS')

@push('styles')
<style>
    body {
        min-height: 100vh;
        background: linear-gradient(rgba(15, 23, 42, 0.50), rgba(15, 23, 42, 0.65)),
                    url('{{ asset('images/telkom-campus.png') }}?v=2') no-repeat center center fixed !important;
        background-size: cover !important;
    }

    .register-container {
        max-width: 480px;
        width: 100%;
        margin: 1.5rem auto;
    }

    .register-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.04);
        padding: 2rem 1.75rem;
    }

    .register-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .register-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--slate-900);
        margin: 0;
        line-height: 1.3;
    }

    .register-subtitle {
        font-size: 0.8125rem;
        color: var(--slate-500);
        margin-top: 0.25rem;
    }

    .register-form .form-group {
        margin-bottom: 1rem;
    }

    .register-form .form-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--slate-700);
        margin-bottom: 0.375rem;
    }

    .register-form .form-control {
        width: 100%;
        padding: 0.5625rem 0.875rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.875rem;
        background-color: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .register-form .form-control:focus {
        outline: none;
        border-color: var(--tel-red);
        box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.12);
    }

    .btn-telkom {
        width: 100%;
        padding: 0.6875rem 1rem;
        background-color: var(--tel-red);
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.15s ease, transform 0.05s ease;
    }

    .btn-telkom:hover {
        background-color: var(--tel-red-dark);
    }
</style>
@endpush

@section('content')
<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <h1 class="register-title">Pendaftaran Akun Mahasiswa</h1>
            <p class="register-subtitle">Daftarkan akun praktikan Anda untuk bergabung ke perkuliahan</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.25rem; padding: 0.75rem 1rem;">
                <div style="font-weight: 600; margin-bottom: 0.25rem;">Pendaftaran Gagal:</div>
                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.8125rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="register-form">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">
                    Nama Lengkap Mahasiswa <span style="color: var(--tel-red);">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="form-control @error('name') is-invalid @enderror" 
                    value="{{ old('name') }}" 
                    placeholder="Contoh: Muhammad Raihan Pratama" 
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="nomor_induk" class="form-label">
                    Nomor Induk Mahasiswa (NIM) <span style="color: var(--tel-red);">*</span>
                </label>
                <input 
                    type="text" 
                    name="nomor_induk" 
                    id="nomor_induk" 
                    class="form-control @error('nomor_induk') is-invalid @enderror" 
                    value="{{ old('nomor_induk') }}" 
                    placeholder="Contoh: 1301210099" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    Alamat Email Kampus / Aktif <span style="color: var(--tel-red);">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    value="{{ old('email') }}" 
                    placeholder="nama@student.telkomuniversity.ac.id" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    Kata Sandi <span style="color: var(--tel-red);">*</span>
                </label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    placeholder="Minimal 6 karakter" 
                    required
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    Konfirmasi Kata Sandi <span style="color: var(--tel-red);">*</span>
                </label>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    class="form-control" 
                    placeholder="Ulangi kata sandi" 
                    required
                >
            </div>

                <button type="submit" class="btn-telkom" style="margin-top: 0.5rem;">
                    Daftar Akun Sekarang
                </button>

            <div style="text-align: center; margin-top: 1.25rem; font-size: 0.875rem; color: var(--slate-600);">
                Sudah memiliki akun? <a href="{{ route('login') }}" style="color: var(--tel-red); font-weight: 600; text-decoration: none;">Masuk ke CeLOE LMS &rarr;</a>
            </div>
        </form>
    </div>
</div>
@endsection
