@extends('layouts.app')

@section('title', 'Laporan Stok')
@section('page-title', 'Laporan Stok')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-boxes text-primary me-2"></i>
            Laporan Stok Produk
        </h4>
        <p class="text-muted mb-0">Monitor ketersediaan stok semua produk</p>
    </div>
    <div>
        <button class="btn btn-success" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>
            Cetak Laporan
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    @php
        $total_produk = count($produk ?? []);
        $stok_habis = collect($produk ?? [])->where('stok', 0)->count();
        $stok_menipis = collect($produk ?? [])->filter(fn($p) => $p['stok'] > 0 && $p['stok'] < 10)->count();
        $stok_aman = collect($produk ?? [])->where('stok', '>=', 10)->count();
    @endphp

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Total Produk</h6>
                        <h3 class="mb-0">{{ $total_produk }}</h3>
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
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Stok Aman</h6>
                        <h3 class="mb-0">{{ $stok_aman }}</h3>
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
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Stok Menipis</h6>
                        <h3 class="mb-0">{{ $stok_menipis }}</h3>
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
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Stok Habis</h6>
                        <h3 class="mb-0">{{ $stok_habis }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Stok -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Detail Stok Produk
        </h6>
    </div>
    <div class="card-body p-0">
        @if(isset($produk) && count($produk) > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="5%">No</th>
                        <th width="10%">ID</th>
                        <th width="35%">Nama Produk</th>
                        <th width="15%">Jenis</th>
                        <th width="15%">Harga</th>
                        <th width="10%">Stok</th>
                        <th width="10%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produk as $index => $p)
                    <tr class="{{ $p['stok'] == 0 ? 'table-danger' : ($p['stok'] < 10 ? 'table-warning' : '') }}">
                        <td class="ps-4">{{ $index + 1 }}</td>
                        <td><strong class="text-primary">#{{ $p['id'] }}</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($p['jenis'] == 'elpiji')
                                <div class="bg-danger bg-opacity-10 text-danger rounded p-2 me-2">
                                    <i class="bi bi-fire"></i>
                                </div>
                                @else
                                <div class="bg-info bg-opacity-10 text-info rounded p-2 me-2">
                                    <i class="bi bi-droplet-fill"></i>
                                </div>
                                @endif
                                <strong>{{ $p['nama_produk'] }}</strong>
                            </div>
                        </td>
                        <td>
                            @if($p['jenis'] == 'elpiji')
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-fire me-1"></i>
                                Gas Elpiji
                            </span>
                            @else
                            <span class="badge bg-info bg-opacity-10 text-info">
                                <i class="bi bi-droplet-fill me-1"></i>
                                Air Galon
                            </span>
                            @endif
                        </td>
                        <td><strong class="text-success">Rp {{ number_format($p['harga'], 0, ',', '.') }}</strong></td>
                        <td>
                            <strong class="fs-5 
                                {{ $p['stok'] == 0 ? 'text-danger' : ($p['stok'] < 10 ? 'text-warning' : 'text-success') }}">
                                {{ $p['stok'] }}
                            </strong>
                            <small class="text-muted">pcs</small>
                        </td>
                        <td>
                            @if($p['stok'] == 0)
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                Habis
                            </span>
                            @elseif($p['stok'] < 10)
                            <span class="badge bg-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Menipis
                            </span>
                            @else
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Aman
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-1"></i>
            <p class="mb-0 mt-3">Belum ada data produk</p>
        </div>
        @endif
    </div>
</div>

<!-- Alert Stok Menipis -->
@if($stok_menipis > 0 || $stok_habis > 0)
<div class="alert alert-warning mt-4" role="alert">
    <h6 class="alert-heading">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Peringatan Stok
    </h6>
    <p class="mb-0">
        @if($stok_habis > 0)
        <strong>{{ $stok_habis }}</strong> produk dengan stok habis dan 
        @endif
        @if($stok_menipis > 0)
        <strong>{{ $stok_menipis }}</strong> produk dengan stok menipis. 
        Segera lakukan restock!
        @endif
    </p>
</div>
@endif

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
    
    @media print {
        .btn, nav, .sidebar { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endpush