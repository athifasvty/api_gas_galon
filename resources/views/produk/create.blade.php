@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
<!-- Header Section -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('produk.index') }}">Data Produk</a></li>
            <li class="breadcrumb-item active">Tambah Produk</li>
        </ol>
    </nav>
    <h4 class="mb-0">
        <i class="bi bi-plus-circle text-primary me-2"></i>
        Tambah Produk Baru
    </h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Form Data Produk
                </h6>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('produk.store') }}" method="POST" id="produkForm">
                    @csrf

                    <!-- Jenis Produk -->
                    <div class="mb-4">
                        <label class="form-label">
                            Jenis Produk <span class="text-danger">*</span>
                        </label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="jenis" id="jenis_elpiji" value="elpiji" 
                                       {{ old('jenis') == 'elpiji' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-danger w-100 py-3" for="jenis_elpiji">
                                    <i class="bi bi-fire fs-3 d-block mb-2"></i>
                                    <strong>Gas Elpiji</strong>
                                    <small class="d-block text-muted">3kg, 12kg, dll</small>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="jenis" id="jenis_galon" value="galon"
                                       {{ old('jenis') == 'galon' ? 'checked' : '' }}>
                                <label class="btn btn-outline-info w-100 py-3" for="jenis_galon">
                                    <i class="bi bi-droplet-fill fs-3 d-block mb-2"></i>
                                    <strong>Air Galon</strong>
                                    <small class="d-block text-muted">Aqua, Le Minerale, dll</small>
                                </label>
                            </div>
                        </div>
                        @error('jenis')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Produk -->
                    <div class="mb-3">
                        <label for="nama_produk" class="form-label">
                            Nama Produk <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-box-seam"></i>
                            </span>
                            <input type="text" 
                                   class="form-control @error('nama_produk') is-invalid @enderror" 
                                   id="nama_produk" 
                                   name="nama_produk" 
                                   placeholder="Contoh: Elpiji 3kg, Air Galon Aqua"
                                   value="{{ old('nama_produk') }}"
                                   required>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Masukkan nama produk yang jelas dan deskriptif
                        </small>
                        @error('nama_produk')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Harga -->
                    <div class="mb-3">
                        <label for="harga" class="form-label">
                            Harga <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <strong>Rp</strong>
                            </span>
                            <input type="number" 
                                   class="form-control @error('harga') is-invalid @enderror" 
                                   id="harga" 
                                   name="harga" 
                                   placeholder="0"
                                   value="{{ old('harga') }}"
                                   min="0"
                                   required>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Harga dalam rupiah (tanpa titik atau koma)
                        </small>
                        @error('harga')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Stok -->
                    <div class="mb-4">
                        <label for="stok" class="form-label">
                            Stok Awal <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-boxes"></i>
                            </span>
                            <input type="number" 
                                   class="form-control @error('stok') is-invalid @enderror" 
                                   id="stok" 
                                   name="stok" 
                                   placeholder="0"
                                   value="{{ old('stok') }}"
                                   min="0"
                                   required>
                            <span class="input-group-text">pcs</span>
                        </div>
                        @error('stok')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>
                            Simpan
                        </button>
                        <a href="{{ route('produk.index') }}" class="btn btn-secondary">
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
        <div class="card border-0 shadow-sm bg-light mb-3">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    Informasi
                </h6>
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Pilih jenis produk terlebih dahulu
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Nama produk harus jelas dan deskriptif
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Harga dalam rupiah (angka saja)
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Stok bisa diupdate kapan saja
                    </li>
                </ul>
            </div>
        </div>

        <!-- Contoh Produk -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="bi bi-lightbulb text-warning me-2"></i>
                    Contoh Produk
                </h6>
                <div class="mb-3">
                    <strong class="text-danger">
                        <i class="bi bi-fire me-1"></i>
                        Gas Elpiji:
                    </strong>
                    <ul class="small mt-2 mb-0">
                        <li>Elpiji 3kg - Rp 25.000</li>
                        <li>Elpiji 12kg - Rp 150.000</li>
                    </ul>
                </div>
                <div>
                    <strong class="text-info">
                        <i class="bi bi-droplet-fill me-1"></i>
                        Air Galon:
                    </strong>
                    <ul class="small mt-2 mb-0">
                        <li>Air Galon Aqua - Rp 20.000</li>
                        <li>Air Galon Le Minerale - Rp 19.000</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto format harga dengan thousand separator
    document.getElementById('harga').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });

    // Preview harga dengan format
    document.getElementById('harga').addEventListener('blur', function(e) {
        if (e.target.value) {
            let value = parseInt(e.target.value);
            console.log('Harga: Rp ' + value.toLocaleString('id-ID'));
        }
    });
</script>
@endpush