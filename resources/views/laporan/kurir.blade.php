@extends('layouts.app')

@section('title', 'Laporan Performa Kurir')
@section('page-title', 'Laporan Performa Kurir')

@section('content')
<!-- Header -->
<div class="mb-4">
    <h4 class="mb-1">
        <i class="bi bi-truck text-primary me-2"></i>
        Laporan Performa Kurir
    </h4>
    <p class="text-muted mb-0">Evaluasi kinerja kurir berdasarkan periode</p>
</div>

<!-- Filter Period -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('laporan.kurir') }}" class="row g-3 align-items-end">
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

<!-- Performa Kurir -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>
                Data Performa Kurir
            </h6>
            <button class="btn btn-sm btn-success" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>
                Cetak
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        @if(isset($kurir) && count($kurir) > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="5%">Rank</th>
                        <th width="25%">Nama Kurir</th>
                        <th width="15%">No. Telepon</th>
                        <th class="text-center" width="10%">Total Pesanan</th>
                        <th class="text-center" width="10%">Diproses</th>
                        <th class="text-center" width="10%">Dikirim</th>
                        <th class="text-center" width="10%">Selesai</th>
                        <th class="text-end" width="15%">Nilai Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kurir as $index => $k)
                    <tr>
                        <td class="ps-4">
                            @if($index == 0)
                            <i class="bi bi-trophy-fill text-warning fs-4"></i>
                            @elseif($index == 1)
                            <i class="bi bi-trophy-fill text-secondary fs-5"></i>
                            @elseif($index == 2)
                            <i class="bi bi-trophy-fill text-danger fs-6"></i>
                            @else
                            <span class="fs-5">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-2" 
                                     style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <strong>{{ $k['name'] }}</strong>
                            </div>
                        </td>
                        <td>
                            <i class="bi bi-telephone me-1"></i>
                            {{ $k['phone'] ?? '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6">{{ $k['total_pesanan'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $k['pesanan_diproses'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">{{ $k['pesanan_dikirim'] }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success fs-6">{{ $k['pesanan_selesai'] }}</span>
                        </td>
                        <td class="text-end">
                            <strong class="text-success">
                                Rp {{ number_format($k['total_nilai_pesanan'], 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr>
                        <td colspan="3" class="ps-4"><strong>TOTAL</strong></td>
                        <td class="text-center">
                            <strong>{{ array_sum(array_column($kurir, 'total_pesanan')) }}</strong>
                        </td>
                        <td class="text-center">
                            <strong>{{ array_sum(array_column($kurir, 'pesanan_diproses')) }}</strong>
                        </td>
                        <td class="text-center">
                            <strong>{{ array_sum(array_column($kurir, 'pesanan_dikirim')) }}</strong>
                        </td>
                        <td class="text-center">
                            <strong>{{ array_sum(array_column($kurir, 'pesanan_selesai')) }}</strong>
                        </td>
                        <td class="text-end">
                            <strong class="text-success">
                                Rp {{ number_format(array_sum(array_column($kurir, 'total_nilai_pesanan')), 0, ',', '.') }}
                            </strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox display-1"></i>
            <p class="mb-0 mt-3">Tidak ada data kurir pada periode ini</p>
        </div>
        @endif
    </div>
</div>

<!-- Performance Chart (Optional) -->
@if(isset($kurir) && count($kurir) > 0)
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart me-2"></i>
                    Top 5 Kurir Terbaik
                </h6>
            </div>
            <div class="card-body">
                @foreach(array_slice($kurir, 0, 5) as $index => $k)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small><strong>{{ $k['name'] }}</strong></small>
                        <small class="text-muted">{{ $k['pesanan_selesai'] }} pesanan</small>
                    </div>
                    <div class="progress" style="height: 20px;">
                        @php
                            $max = $kurir[0]['pesanan_selesai'] ?? 1;
                            $percentage = ($k['pesanan_selesai'] / max($max, 1)) * 100;
                        @endphp
                        <div class="progress-bar bg-success" 
                             role="progressbar" 
                             style="width: {{ $percentage }}%"
                             aria-valuenow="{{ $percentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ round($percentage) }}%
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Catatan
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Total Pesanan:</strong> Total semua pesanan yang di-assign ke kurir
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-info me-2"></i>
                        <strong>Diproses:</strong> Pesanan yang sedang diproses
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-warning me-2"></i>
                        <strong>Dikirim:</strong> Pesanan dalam pengiriman
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        <strong>Selesai:</strong> Pesanan yang berhasil diselesaikan
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-primary me-2"></i>
                        <strong>Nilai Pesanan:</strong> Total nilai dari pesanan yang selesai
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    @media print {
        .btn, nav, .sidebar, form { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; page-break-inside: avoid; }
        .row.mt-4 { display: none !important; }
    }
</style>
@endpush