@extends('layouts.app')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan')

@section('content')
<!-- Header Section -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pesanan.index') }}">Data Pesanan</a></li>
            <li class="breadcrumb-item active">Detail Pesanan</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="bi bi-receipt text-primary me-2"></i>
            Detail Pesanan #{{ $pesanan['id'] }}
        </h4>
        <div>
            @if(in_array($pesanan['status'], ['dibatalkan', 'selesai']))
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="bi bi-trash me-2"></i>
                Hapus Pesanan
            </button>
            @endif
            <a href="{{ route('pesanan.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Kembali
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Info Pesanan -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Informasi Pesanan
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted">ID Pesanan</label>
                        <div><strong class="text-primary">#{{ $pesanan['id'] }}</strong></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted">Tanggal Pesan</label>
                        <div><strong>{{ date('d M Y, H:i', strtotime($pesanan['tanggal_pesan'])) }}</strong></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted">Status Pesanan</label>
                        <div>
                            @if($pesanan['status'] == 'menunggu')
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-clock me-1"></i>
                                Menunggu
                            </span>
                            @elseif($pesanan['status'] == 'diproses')
                            <span class="badge bg-info">
                                <i class="bi bi-gear me-1"></i>
                                Diproses
                            </span>
                            @elseif($pesanan['status'] == 'dikirim')
                            <span class="badge bg-primary">
                                <i class="bi bi-truck me-1"></i>
                                Dikirim
                            </span>
                            @elseif($pesanan['status'] == 'selesai')
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
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="small text-muted">Total Harga</label>
                        <div><strong class="text-success fs-5">Rp {{ number_format($pesanan['total_harga'], 0, ',', '.') }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bukti Pengiriman (BARU!) -->
        @if(!empty($pesanan['bukti_pengiriman']))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-camera me-2"></i>
                    Bukti Pengiriman
                </h6>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="bukti-image-container position-relative">
                            <img src="http://192.168.203.206/api_gas_galon/uploads/bukti_pengiriman/{{ $pesanan['bukti_pengiriman'] }}" 
                                 class="img-fluid rounded shadow-sm" 
                                 alt="Bukti Pengiriman"
                                 style="max-height: 300px; width: 100%; object-fit: cover; cursor: pointer;"
                                 data-bs-toggle="modal" 
                                 data-bs-target="#buktiModal">
                            <div class="position-absolute top-0 end-0 m-2">
                                <button class="btn btn-sm btn-light rounded-circle" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#buktiModal"
                                        title="Lihat Fullscreen">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Bukti Tersedia</strong>
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Waktu Upload</label>
                            <div>
                                <i class="bi bi-clock me-1"></i>
                                <strong>{{ date('d M Y, H:i', strtotime($pesanan['waktu_upload_bukti'])) }}</strong>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="small text-muted">Diupload Oleh</label>
                            <div>
                                <i class="bi bi-person-badge me-1"></i>
                                <strong>{{ $pesanan['nama_kurir'] ?? 'Kurir' }}</strong>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="http://192.168.203.206/api_gas_galon/uploads/bukti_pengiriman/{{ $pesanan['bukti_pengiriman'] }}" 
                               class="btn btn-sm btn-outline-success" 
                               download>
                                <i class="bi bi-download me-2"></i>
                                Download Bukti
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @elseif($pesanan['status'] == 'selesai')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Bukti Pengiriman Tidak Tersedia</strong>
                    <p class="mb-0 small">Pesanan selesai tanpa bukti foto pengiriman.</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Customer Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-person me-2"></i>
                    Data Customer
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="small text-muted">Nama Customer</label>
                    <div><strong>{{ $pesanan['nama_customer'] }}</strong></div>
                </div>
                <div class="mb-2">
                    <label class="small text-muted">No. Telepon</label>
                    <div>
                        <i class="bi bi-telephone me-1"></i>
                        <a href="tel:{{ $pesanan['phone_customer'] }}">{{ $pesanan['phone_customer'] }}</a>
                    </div>
                </div>
                <div>
                    <label class="small text-muted">Alamat Pengiriman</label>
                    <div>
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ $pesanan['alamat_customer'] ?: 'Alamat tidak tersedia' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-box-seam me-2"></i>
                    Detail Produk
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Produk</th>
                                <th>Jenis</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pesanan['items'] as $item)
                            <tr>
                                <td class="ps-3">
                                    <strong>{{ $item['nama_produk'] }}</strong>
                                </td>
                                <td>
                                    @if($item['jenis'] == 'elpiji')
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
                                <td>Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                                <td>{{ $item['jumlah'] }} pcs</td>
                                <td class="text-end pe-3">
                                    <strong class="text-success">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="4" class="text-end ps-3"><strong>TOTAL</strong></td>
                                <td class="text-end pe-3">
                                    <strong class="text-success fs-5">
                                        Rp {{ number_format($pesanan['total_harga'], 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Assign Kurir (Hanya untuk status menunggu) -->
        @if($pesanan['status'] == 'menunggu')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-person-plus me-2"></i>
                    Assign Kurir
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Pilih kurir untuk mengirim pesanan ini
                </p>
                <form action="{{ route('pesanan.assign-kurir', $pesanan['id']) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih Kurir <span class="text-danger">*</span></label>
                        <select name="id_kurir" class="form-select" required>
                            <option value="">-- Pilih Kurir --</option>
                            @foreach($kurir_list as $k)
                            <option value="{{ $k['id'] }}">
                                {{ $k['name'] }} - {{ $k['phone'] ?? 'No phone' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>
                        Assign Kurir
                    </button>
                </form>
            </div>
        </div>
        @else
        <!-- Info Kurir (Untuk status selain menunggu) -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-person-badge me-2"></i>
                    Info Kurir
                </h6>
            </div>
            <div class="card-body">
                @if($pesanan['nama_kurir'])
                <div class="text-center mb-3">
                    <div class="avatar-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2" 
                         style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-fill fs-3"></i>
                    </div>
                    <h6 class="mb-1">{{ $pesanan['nama_kurir'] }}</h6>
                    <small class="text-muted">
                        <i class="bi bi-telephone me-1"></i>
                        <a href="tel:{{ $pesanan['phone_kurir'] }}">{{ $pesanan['phone_kurir'] }}</a>
                    </small>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Kurir sudah di-assign untuk pesanan ini
                </div>
                @else
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-person-x fs-1"></i>
                    <p class="mb-0 small mt-2">Belum ada kurir yang di-assign</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Update Status (Untuk status diproses dan dikirim) -->
        @if(in_array($pesanan['status'], ['diproses', 'dikirim']))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    Update Status
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Ubah status pesanan sesuai progres pengiriman
                </p>
                <form action="{{ route('pesanan.update-status', $pesanan['id']) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Status Baru <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="">-- Pilih Status --</option>
                            @if($pesanan['status'] == 'diproses')
                            <option value="dikirim">
                                <i class="bi bi-truck"></i> Dikirim
                            </option>
                            @elseif($pesanan['status'] == 'dikirim')
                            <option value="selesai">
                                <i class="bi bi-check-circle"></i> Selesai
                            </option>
                            @endif
                            <option value="dibatalkan">
                                <i class="bi bi-x-circle"></i> Dibatalkan
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-2"></i>
                        Update Status
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Status Selesai/Dibatalkan -->
        @if(in_array($pesanan['status'], ['selesai', 'dibatalkan']))
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center py-4">
                @if($pesanan['status'] == 'selesai')
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                <h6 class="text-success mb-2">Pesanan Selesai</h6>
                <p class="small text-muted mb-0">Pesanan telah selesai dan diterima customer</p>
                @else
                <i class="bi bi-x-circle-fill text-danger fs-1 mb-3"></i>
                <h6 class="text-danger mb-2">Pesanan Dibatalkan</h6>
                <p class="small text-muted mb-0">Pesanan ini telah dibatalkan</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Payment Info -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0">
                    <i class="bi bi-credit-card me-2"></i>
                    Info Pembayaran
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="small text-muted">Metode Pembayaran</label>
                    <div>
                        <strong>{{ ucfirst($pesanan['metode_bayar'] ?? 'Cash') }}</strong>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="small text-muted">Status Pembayaran</label>
                    <div>
                        @if($pesanan['status_bayar'] == 'sudah_bayar')
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Sudah Dibayar
                        </span>
                        @else
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-clock me-1"></i>
                            Belum Dibayar
                        </span>
                        @endif
                    </div>
                </div>
                @if($pesanan['tanggal_bayar'])
                <div>
                    <label class="small text-muted">Tanggal Bayar</label>
                    <div><small>{{ date('d M Y, H:i', strtotime($pesanan['tanggal_bayar'])) }}</small></div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bukti Modal (Fullscreen) -->
@if(!empty($pesanan['bukti_pengiriman']))
<div class="modal fade" id="buktiModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>
                    Bukti Pengiriman - Pesanan #{{ $pesanan['id'] }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="{{ asset('uploads/bukti_pengiriman/' . $pesanan['bukti_pengiriman']) }}" 
                     class="img-fluid" 
                     alt="Bukti Pengiriman"
                     style="max-height: 80vh; width: auto;">
            </div>
            <div class="modal-footer border-0">
                <a href="{{ asset('uploads/bukti_pengiriman/' . $pesanan['bukti_pengiriman']) }}" 
                   class="btn btn-success" 
                   download>
                    <i class="bi bi-download me-2"></i>
                    Download
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Apakah Anda yakin ingin menghapus pesanan <strong>#{{ $pesanan['id'] }}</strong>?
                </p>
                <div class="alert alert-warning small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Data yang sudah dihapus tidak dapat dikembalikan.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('pesanan.destroy', $pesanan['id']) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .card {
        transition: all 0.3s ease;
    }
    
    .avatar-circle {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .bukti-image-container img:hover {
        opacity: 0.9;
        transform: scale(1.02);
        transition: all 0.3s ease;
    }
</style>
@endpush