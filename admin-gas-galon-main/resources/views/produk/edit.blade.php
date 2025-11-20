@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
<!-- Header Section -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('produk.index') }}">Data Produk</a></li>
            <li class="breadcrumb-item active">Edit Produk</li>
        </ol>
    </nav>
    <h4 class="mb-0">
        <i class="bi bi-pencil text-primary me-2"></i>
        Edit Data Produk
    </h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-clipboard-data me-2"></i>
                    Form Edit Produk
                </h6>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('produk.update', $produk['id']) }}" method="POST" id="produkForm">
                    @csrf
                    @method('PUT')

                    <!-- Jenis Produk -->
                    <div class="mb-4">
                        <label class="form-label">
                            Jenis Produk <span class="text-danger">*</span>
                        </label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="jenis" id="jenis_elpiji" value="elpiji" 
                                       {{ old('jenis', $produk['jenis']) == 'elpiji' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-danger w-100 py-3" for="jenis_elpiji">
                                    <i class="bi bi-fire fs-3 d-block mb-2"></i>
                                    <strong>Gas Elpiji</strong>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="jenis" id="jenis_galon" value="galon"
                                       {{ old('jenis', $produk['jenis']) == 'galon' ? 'checked' : '' }}>
                                <label class="btn btn-outline-info w-100 py-3" for="jenis_galon">
                                    <i class="bi bi-droplet-fill fs-3 d-block mb-2"></i>
                                    <strong>Air Galon</strong>
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
                                   value="{{ old('nama_produk', $produk['nama_produk']) }}"
                                   required>
                        </div>
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
                                   value="{{ old('harga', $produk['harga']) }}"
                                   min="0"
                                   required>
                        </div>
                        @error('harga')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Stok -->
                    <div class="mb-4">
                        <label for="stok" class="form-label">
                            Stok <span class="text-danger">*</span>
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
                                   value="{{ old('stok', $produk['stok']) }}"
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
                            Update
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
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    Informasi Produk
                </h6>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted">ID Produk:</td>
                        <td><strong>#{{ $produk['id'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat:</td>
                        <td>{{ $produk['created_at'] ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection