@extends('layouts.app')

@section('title', 'Laporan Overview')
@section('page-title', 'Laporan Overview')

@section('content')
<!-- Page Title -->
<div class="mb-4">
    <h4 class="fw-bold">Laporan Overview</h4>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="laporanTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
            Laporan Overview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('laporan.stok') }}">Laporan Stok</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('laporan.transaksi') }}">Laporan Transaksi</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('laporan.kurir') }}">Performa Kurir</a>
    </li>
</ul>

<div class="tab-content" id="laporanTabsContent">
    <div class="tab-pane fade show active" id="overview" role="tabpanel">

<!-- Statistics Cards Row 1 -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Pesanan Hari Ini</h6>
                        <h3 class="mb-0">{{ $data['pesanan_hari_ini'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Pendapatan Hari Ini</h6>
                        <h3 class="mb-0">Rp {{ number_format($data['pendapatan_hari_ini'] ?? 0, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Pesanan Pending</h6>
                        <h3 class="mb-0">{{ $data['pesanan_pending'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1 small">Stok Menipis</h6>
                        <h3 class="mb-0">{{ $data['produk_stok_menipis'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards Row 2 -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-people-fill fs-1"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75">Total Customer</h6>
                        <h2 class="mb-0">{{ $data['total_customer'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-person-badge-fill fs-1"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75">Total Kurir</h6>
                        <h2 class="mb-0">{{ $data['total_kurir'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-box-seam-fill fs-1"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 opacity-75">Total Produk</h6>
                        <h2 class="mb-0">{{ $data['total_produk'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Produk Terlaris -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-trophy text-warning me-2"></i>
                        Produk Terlaris Bulan Ini
                    </h6>
                    <span class="badge bg-warning text-dark">Top 5</span>
                </div>
            </div>
            <div class="card-body">
                @if(isset($data['produk_terlaris']) && count($data['produk_terlaris']) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="50%">Nama Produk</th>
                                <th width="20%">Jenis</th>
                                <th width="25%">Total Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['produk_terlaris'] as $index => $produk)
                            <tr>
                                <td>
                                    @if($index == 0)
                                    <i class="bi bi-trophy-fill text-warning fs-5"></i>
                                    @elseif($index == 1)
                                    <i class="bi bi-trophy-fill text-secondary fs-6"></i>
                                    @elseif($index == 2)
                                    <i class="bi bi-trophy-fill text-danger fs-6"></i>
                                    @else
                                    {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $produk['nama_produk'] }}</strong>
                                </td>
                                <td>
                                    @if($produk['jenis'] == 'elpiji')
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        <i class="bi bi-fire me-1"></i>
                                        Elpiji
                                    </span>
                                    @else
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-droplet-fill me-1"></i>
                                        Galon
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success fs-6">
                                        {{ $produk['total_terjual'] }} pcs
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mb-0 mt-2">Belum ada data penjualan bulan ini</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

    </div>
</div>

@endsection

@push('styles')
<style>
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .card {
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-3px);
    }
</style>
@endpush