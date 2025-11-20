@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Welcome Alert -->
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-check-circle me-2"></i>
                Selamat Datang, {{ session('auth_name', 'Admin') }}!
            </h5>
            <p class="mb-0">Anda login sebagai <strong>{{ strtoupper(session('auth_role', 'Admin')) }}</strong></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Error Alert (jika ada) -->
        @if(isset($error))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h6 class="alert-heading">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Peringatan Koneksi API
            </h6>
            <p class="mb-0">{{ $error }}</p>
            <hr>
            <small>
                Pastikan API PHP sedang berjalan di: <code>{{ config('api.base_url') }}</code>
            </small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Pesanan Hari Ini -->
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Pesanan Hari Ini</h6>
                        <h3 class="mb-0">{{ $pesanan_hari_ini ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendapatan Hari Ini -->
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Pendapatan Hari Ini</h6>
                        <h3 class="mb-0">Rp {{ number_format($pendapatan_hari_ini ?? 0, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pesanan Pending -->
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Pesanan Pending</h6>
                        <h3 class="mb-0">{{ $pesanan_pending ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Menipis -->
    <div class="col-md-6 col-lg-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Stok Menipis</h6>
                        <h3 class="mb-0">{{ $produk_stok_menipis ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pesanan Section -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-3">
                    <i class="bi bi-cart-check text-primary me-2"></i>
                    Daftar Pesanan
                </h5>
                <!-- Tabs for Order Status -->
                <ul class="nav nav-pills" id="orderTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="baru-tab" data-bs-toggle="tab" data-bs-target="#pesanan-baru" type="button" role="tab">
                            Pesanan Baru
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="proses-tab" data-bs-toggle="tab" data-bs-target="#dalam-pengantaran" type="button" role="tab">
                            Dalam Pengantaran
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="selesai-tab" data-bs-toggle="tab" data-bs-target="#selesai" type="button" role="tab">
                            Selesai
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="orderTabsContent">
                    <!-- Pesanan Baru -->
                    <div class="tab-pane fade show active" id="pesanan-baru" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Order</th>
                                        <th>Nama Pelanggan</th>
                                        <th>No. Telepon</th>
                                        <th>Alamat</th>
                                        <th>Kurir</th>
                                        <th>Total</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($pesanan_baru) && count($pesanan_baru) > 0)
                                        @foreach($pesanan_baru as $order)
                                        <tr>
                                            <td><a href="{{ route('pesanan.show', $order['id']) }}">#{{ $order['id'] }}</a></td>
                                            <td>{{ $order['nama_customer'] }}</td>
                                            <td>{{ $order['phone_customer'] }}</td>
                                            <td>{{ $order['alamat_customer'] ?? '-' }}</td>
                                            <td>{{ $order['nama_kurir'] ?? '-' }}</td>
                                            <td>Rp {{ number_format($order['total_harga'], 0, ',', '.') }}</td>
                                            <td>{{ date('d/m/Y', strtotime($order['tanggal_pesan'])) }}</td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox fs-1"></i>
                                                <p class="mb-0 mt-2">Tidak ada pesanan baru</p>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Dalam Pengantaran -->
                    <div class="tab-pane fade" id="dalam-pengantaran" role="tabpanel">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0 mt-2">Tidak ada pesanan dalam pengantaran</p>
                        </div>
                    </div>
                    
                    <!-- Selesai -->
                    <div class="tab-pane fade" id="selesai" role="tabpanel">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mb-0 mt-2">Belum ada pesanan selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="bi bi-lightning text-primary me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('produk.create') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-plus-circle me-2"></i>
                            Tambah Produk
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('pesanan.index') }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-cart-check me-2"></i>
                            Lihat Pesanan
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('kurir.create') }}" class="btn btn-outline-info w-100">
                            <i class="bi bi-person-plus me-2"></i>
                            Tambah Kurir
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('laporan.overview') }}" class="btn btn-outline-warning w-100">
                            <i class="bi bi-graph-up me-2"></i>
                            Lihat Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
</style>
@endpush