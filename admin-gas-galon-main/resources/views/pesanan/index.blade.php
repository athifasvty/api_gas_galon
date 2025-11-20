@extends('layouts.app')

@section('title', 'Data Pesanan')
@section('page-title', 'Data Pesanan')

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-cart-check text-primary me-2"></i>
            Manajemen Pesanan
        </h4>
        <p class="text-muted mb-0">Kelola semua pesanan pelanggan</p>
    </div>
</div>

<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('pesanan.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Filter Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>
                        Menunggu
                    </option>
                    <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>
                        Diproses
                    </option>
                    <option value="dikirim" {{ request('status') == 'dikirim' ? 'selected' : '' }}>
                        Dikirim
                    </option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>
                        Selesai
                    </option>
                    <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>
                        Dibatalkan
                    </option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Dari Tanggal</label>
                <input type="date" name="tanggal_dari" class="form-select" value="{{ request('tanggal_dari') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai" class="form-select" value="{{ request('tanggal_sampai') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-2"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Pesanan List Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Pesanan
                </h6>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary">
                    {{ isset($pesanan) ? count($pesanan) : 0 }} Pesanan
                </span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if(isset($pesanan) && count($pesanan) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="8%">ID Order</th>
                        <th width="15%">Customer</th>
                        <th width="12%">Tanggal</th>
                        <th width="10%">Total</th>
                        <th width="12%">Kurir</th>
                        <th width="10%">Status</th>
                        <th width="8%">Items</th>
                        <th class="text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pesanan as $p)
                    <tr>
                        <td class="ps-4">
                            <strong class="text-primary">#{{ $p['id'] }}</strong>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $p['nama_customer'] }}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-telephone me-1"></i>
                                    {{ $p['phone_customer'] }}
                                </small>
                            </div>
                        </td>
                        <td>
                            <small>{{ date('d M Y, H:i', strtotime($p['tanggal_pesan'])) }}</small>
                        </td>
                        <td>
                            <strong class="text-success">
                                Rp {{ number_format($p['total_harga'], 0, ',', '.') }}
                            </strong>
                        </td>
                        <td>
                            @if($p['nama_kurir'])
                            <div>
                                <i class="bi bi-person-badge text-primary me-1"></i>
                                <small>{{ $p['nama_kurir'] }}</small>
                            </div>
                            @else
                            <span class="badge bg-secondary">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($p['status'] == 'menunggu')
                            <span class="badge bg-warning">
                                <i class="bi bi-clock me-1"></i>
                                Menunggu
                            </span>
                            @elseif($p['status'] == 'diproses')
                            <span class="badge bg-info">
                                <i class="bi bi-gear me-1"></i>
                                Diproses
                            </span>
                            @elseif($p['status'] == 'dikirim')
                            <span class="badge bg-primary">
                                <i class="bi bi-truck me-1"></i>
                                Dikirim
                            </span>
                            @elseif($p['status'] == 'selesai')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Selesai
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                Dibatalkan
                            </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ $p['jumlah_item'] }} item
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('pesanan.show', $p['id']) }}" 
                               class="btn btn-sm btn-primary"
                               title="Detail">
                                <i class="bi bi-eye"></i>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted mb-3"></i>
            <h5 class="text-muted">Belum Ada Pesanan</h5>
            <p class="text-muted mb-0">Pesanan dari customer akan muncul di sini</p>
        </div>
        @endif
    </div>
</div>

@endsection