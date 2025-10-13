<div class="sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <div class="sidebar-logo">
                <i class="bi bi-fire text-danger fs-2"></i>
            </div>
            <div class="ms-2">
                <h5 class="mb-0 text-white">Gas Galon</h5>
                <small class="text-white-50">Admin Panel</small>
            </div>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <ul class="sidebar-menu">
        <!-- Dashboard -->
        <li class="menu-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Menu Section: Data Master -->
        <li class="menu-header">
            <span>DATA MASTER</span>
        </li>

        <!-- Produk -->
        <li class="menu-item {{ Request::routeIs('produk.*') ? 'active' : '' }}">
            <a href="{{ route('produk.index') }}" class="menu-link">
                <i class="bi bi-box-seam"></i>
                <span>Produk</span>
            </a>
        </li>

        <!-- Kurir -->
        <li class="menu-item {{ Request::routeIs('kurir.*') ? 'active' : '' }}">
            <a href="{{ route('kurir.index') }}" class="menu-link">
                <i class="bi bi-person-badge"></i>
                <span>Kurir</span>
            </a>
        </li>

        <!-- Menu Section: Transaksi -->
        <li class="menu-header">
            <span>TRANSAKSI</span>
        </li>

        <!-- Pesanan -->
        <li class="menu-item {{ Request::routeIs('pesanan.*') ? 'active' : '' }}">
            <a href="{{ route('pesanan.index') }}" class="menu-link">
                <i class="bi bi-cart-check"></i>
                <span>Pesanan</span>
                @php
                    // Badge untuk pesanan pending (nanti bisa diganti dengan data real)
                    $pesananPending = 5;
                @endphp
                @if($pesananPending > 0)
                <span class="badge bg-danger rounded-pill ms-auto">{{ $pesananPending }}</span>
                @endif
            </a>
        </li>

        <!-- Menu Section: Laporan -->
        <li class="menu-header">
            <span>LAPORAN</span>
        </li>

        <!-- Laporan Overview -->
        <li class="menu-item {{ Request::routeIs('laporan.overview') ? 'active' : '' }}">
            <a href="{{ route('laporan.overview') }}" class="menu-link">
                <i class="bi bi-graph-up"></i>
                <span>Overview</span>
            </a>
        </li>

        <!-- Laporan Stok -->
        <li class="menu-item {{ Request::routeIs('laporan.stok') ? 'active' : '' }}">
            <a href="{{ route('laporan.stok') }}" class="menu-link">
                <i class="bi bi-boxes"></i>
                <span>Laporan Stok</span>
            </a>
        </li>

        <!-- Laporan Transaksi -->
        <li class="menu-item {{ Request::routeIs('laporan.transaksi') ? 'active' : '' }}">
            <a href="{{ route('laporan.transaksi') }}" class="menu-link">
                <i class="bi bi-receipt"></i>
                <span>Laporan Transaksi</span>
            </a>
        </li>

        <!-- Laporan Kurir -->
        <li class="menu-item {{ Request::routeIs('laporan.kurir') ? 'active' : '' }}">
            <a href="{{ route('laporan.kurir') }}" class="menu-link">
                <i class="bi bi-truck"></i>
                <span>Performa Kurir</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="text-center text-white-50 small">
            <i class="bi bi-shield-check"></i> Secure Connection
        </div>
    </div>
</div>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>