@extends('layouts.app')

@section('title', 'Laporan Transaksi')
@section('page-title', 'Laporan Transaksi')

@section('content')
<!-- Header -->
<div class="mb-4">
    <h4 class="mb-1">
        <i class="bi bi-receipt text-primary me-2"></i>
        Laporan Transaksi
    </h4>
    <p class="text-muted mb-0">Analisis transaksi berdasarkan periode waktu</p>
</div>

<!-- Filter Period -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.transaksi') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Dari Tanggal</label>
                <input type="date" name="tanggal_dari" class="form-control" 
                       value="{{ $tanggal_dari ?? date('Y-m-01') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai" class="form-control" 
                       value="{{ $tanggal_sampai ?? date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-2"></i>
                    Tampilkan Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
@if(isset($data['summary']))
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Total Transaksi</h6>
                        <h3 class="mb-0">{{ $data['summary']['total_transaksi'] ?? 0 }}</h3>
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
                    <div>
                        <h6 class="text-muted mb-1 small">Total Pendapatan</h6>
                        <h3 class="mb-0 text-success">
                            Rp {{ number_format($data['summary']['total_pendapatan'] ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small">Transaksi Selesai</h6>
                        <h3 class="mb-0">{{ $data['summary']['transaksi_selesai'] ?? 0 }}</h3>
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
                        <h6 class="text-muted mb-1 small">Transaksi Dibatalkan</h6>
                        <h3 class="mb-0">{{ $data['summary']['transaksi_dibatalkan'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Detail Transaksi -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                Detail Transaksi per Hari
            </h6>
            <button class="btn btn-sm btn-success" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>
                Cetak
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        @if(isset($data['detail']) && count($data['detail']) > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Total Transaksi</th>
                        <th>Pendapatan</th>
                        <th class="text-center">Pending</th>
                        <th class="text-center">Diproses</th>
                        <th class="text-center">Dikirim</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Dibatalkan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['detail'] as $t)
                    <tr>
                        <td class="ps-4">
                            <strong>{{ date('d M Y', strtotime($t['tanggal'])) }}</strong>
                            <br>
                            <small class="text-muted">{{ date('l', strtotime($t['tanggal'])) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $t['jumlah_transaksi'] }} transaksi</span>
                        </td>
                        <td>
                            <strong class="text-success">
                                Rp {{ number_format($t['total_pendapatan'], 0, ',', '.') }}
                            </strong>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">{{ $t['pending'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $t['diproses'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $t['dikirim'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $t['selesai'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger">{{ $t['dibatalkan'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-1"></i>
            <p class="mb-0 mt-3">Tidak ada transaksi pada periode ini</p>
        </div>
        @endif
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
    
    @media print {
        .btn, nav, .sidebar, form { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endpush