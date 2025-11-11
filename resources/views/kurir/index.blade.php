@extends('layouts.app')

@section('title', 'Data Kurir')
@section('page-title', 'Data Kurir')

@section('content')
<!-- Page Title -->
<div class="mb-4">
    <h4 class="fw-bold">Kelola Data</h4>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4" id="kelolaDataTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="{{ route('produk.index') }}">
            <i class="bi bi-box-seam me-2"></i>
            Produk Hydragas
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="kurir-tab" data-bs-toggle="tab" data-bs-target="#kurir" type="button" role="tab">
            <i class="bi bi-person-badge me-2"></i>
            Kurir Hydragas
        </button>
    </li>
</ul>

<div class="tab-content" id="kelolaDataTabsContent">
    <div class="tab-pane fade show active" id="kurir" role="tabpanel">
        <!-- Header Actions -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('kurir.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Tambah
            </a>
            <div></div>
        </div>

<!-- Kurir List Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Daftar Kurir
                </h6>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary">
                    {{ isset($kurir) ? count($kurir) : 0 }} Kurir
                </span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if(isset($kurir) && count($kurir) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" width="5%">No</th>
                        <th width="25%">Nama Kurir</th>
                        <th width="20%">Username</th>
                        <th width="20%">No. Telepon</th>
                        <th width="15%">Status</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kurir as $index => $k)
                    <tr>
                        <td class="ps-4">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary bg-opacity-10 text-primary me-2" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <strong>{{ $k['name'] }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">
                                <i class="bi bi-at me-1"></i>
                                {{ $k['username'] }}
                            </span>
                        </td>
                        <td>
                            @if(!empty($k['phone']))
                            <span class="text-muted">
                                <i class="bi bi-telephone me-1"></i>
                                {{ $k['phone'] }}
                            </span>
                            @else
                            <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Aktif
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('kurir.edit', $k['id']) }}" 
                                   class="btn btn-outline-primary" 
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-outline-danger btn-delete" 
                                        data-id="{{ $k['id'] }}"
                                        data-name="{{ $k['name'] }}"
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
            <i class="bi bi-person-x display-1 text-muted mb-3"></i>
            <h5 class="text-muted">Belum Ada Data Kurir</h5>
            <p class="text-muted mb-4">Tambahkan kurir pertama Anda sekarang</p>
            <a href="{{ route('kurir.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>
                Tambah Kurir
            </a>
        </div>
        @endif
    </div>
</div>

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
                    Apakah Anda yakin ingin menghapus kurir <strong id="kurirName"></strong>?
                </p>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Kurir yang masih memiliki pesanan aktif tidak dapat dihapus.
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

@push('scripts')
<script>
    // Delete confirmation
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const kurirId = this.getAttribute('data-id');
            const kurirName = this.getAttribute('data-name');
            
            document.getElementById('kurirName').textContent = kurirName;
            document.getElementById('deleteForm').action = `/kurir/${kurirId}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
</script>
@endpush