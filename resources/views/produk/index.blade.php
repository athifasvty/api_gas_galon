@extends('layouts.app')

@section('title', 'Data Produk')
@section('page-title', 'Data Produk')

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-box-seam text-primary me-2"></i>
            Manajemen Produk
        </h4>
        <p class="text-muted mb-0">Kelola data produk gas elpiji dan air galon</p>
    </div>
    <div>
        <a href="{{ route('produk.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>
            Tambah Produk
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('produk.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label small text-muted">Filter Jenis Produk</label>
                <select name="jenis" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="elpiji" {{ request('jenis') == 'elpiji' ? 'selected' : '' }}>Gas Elpiji</option>
                    <option value="galon" {{ request('jenis') == 'galon' ? 'selected' : '' }}>Air Galon</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Status Stok</label>
                <select name="stok" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Stok</option>
                    <option value="habis" {{ request('stok') == 'habis' ? 'selected' : '' }}>Stok Habis</option>
                    <option value="menipis" {{ request('stok') == 'menipis' ? 'selected' : '' }}>Stok Menipis (&lt; 10)</option>
                    <option value="aman" {{ request('stok') == 'aman' ? 'selected' : '' }}>Stok Aman</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">&nbsp;</label>
                <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Produk List Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Produk
                </h6>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary">
                    {{ isset($produk) ? count($produk) : 0 }} Produk
                </span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if(isset($produk) && count($produk) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="5%">No</th>
                        <th width="30%">Nama Produk</th>
                        <th width="15%">Jenis</th>
                        <th width="15%">Harga</th>
                        <th width="10%">Stok</th>
                        <th width="10%">Status</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produk as $index => $p)
                    <tr>
                        <td class="ps-4">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    @if($p['jenis'] == 'elpiji')
                                    <div class="bg-danger bg-opacity-10 text-danger rounded p-2">
                                        <i class="bi bi-fire fs-4"></i>
                                    </div>
                                    @else
                                    <div class="bg-info bg-opacity-10 text-info rounded p-2">
                                        <i class="bi bi-droplet-fill fs-4"></i>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <strong>{{ $p['nama_produk'] }}</strong>
                                    <br>
                                    <small class="text-muted">ID: #{{ $p['id'] }}</small>
                                </div>
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
                        <td>
                            <strong class="text-success">Rp {{ number_format($p['harga'], 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <strong class="fs-5">{{ $p['stok'] }}</strong>
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
                                Tersedia
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('produk.edit', $p['id']) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-danger btn-delete" 
                                        data-id="{{ $p['id'] }}"
                                        data-name="{{ $p['nama_produk'] }}"
                                        title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
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
            <h5 class="text-muted">Belum Ada Data Produk</h5>
            <p class="text-muted mb-4">Tambahkan produk pertama Anda sekarang</p>
            <a href="{{ route('produk.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Tambah Produk
            </a>
        </div>
        @endif
    </div>
</div>

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
                <p class="mb-0">
                    Apakah Anda yakin ingin menghapus produk <strong id="produkName"></strong>?
                </p>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Data yang sudah dihapus tidak dapat dikembalikan.
                </small>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" style="display: inline;">
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
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const produkId = this.getAttribute('data-id');
            const produkName = this.getAttribute('data-name');
            
            document.getElementById('produkName').textContent = produkName;
            document.getElementById('deleteForm').action = `/produk/${produkId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
</script>
@endpush