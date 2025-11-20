@extends('layouts.app')

@section('title', 'Edit Kurir')
@section('page-title', 'Edit Kurir')

@section('content')
<!-- Header Section -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kurir.index') }}">Data Kurir</a></li>
            <li class="breadcrumb-item active">Edit Kurir</li>
        </ol>
    </nav>
    <h4 class="mb-0">
        <i class="bi bi-pencil text-primary me-2"></i>
        Edit Data Kurir
    </h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Form Edit Kurir
                </h6>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('kurir.update', $kurir['id']) }}" method="POST" id="kurirForm">
                    @csrf
                    @method('PUT')

                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Masukkan nama lengkap"
                                   value="{{ old('name', $kurir['name']) }}"
                                   required>
                        </div>
                        @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Username (Read Only) -->
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            Username
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-at"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $kurir['username'] }}"
                                   readonly>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-lock me-1"></i>
                            Username tidak dapat diubah
                        </small>
                    </div>

                    <!-- Password Baru -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Password Baru
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Kosongkan jika tidak ingin mengubah">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Kosongkan jika tidak ingin mengubah password
                        </small>
                        @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- No. Telepon -->
                    <div class="mb-4">
                        <label for="phone" class="form-label">
                            No. Telepon
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-telephone"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="08xxxxxxxxxx"
                                   value="{{ old('phone', $kurir['phone']) }}">
                        </div>
                        @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>
                            Update
                        </button>
                        <a href="{{ route('kurir.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    Informasi
                </h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Username tidak dapat diubah
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Password opsional (hanya jika ingin diubah)
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        No. telepon dapat diubah
                    </li>
                </ul>

                <hr>

                <h6 class="mb-3">
                    <i class="bi bi-clock-history text-info me-2"></i>
                    Riwayat
                </h6>
                <p class="small text-muted mb-0">
                    <strong>Username:</strong> {{ $kurir['username'] }}<br>
                    <strong>Dibuat:</strong> {{ $kurir['created_at'] ?? '-' }}
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Toggle password visibility
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }

    // Auto-format phone number
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });
</script>
@endpush