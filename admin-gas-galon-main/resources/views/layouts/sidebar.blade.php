<div class="sidebar" id="sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header text-center">
        <div class="sidebar-logo-wrapper mb-3">
            <img src="{{ asset('images/logo.png') }}" alt="Hydragas Logo" class="sidebar-logo-img">
        </div>
        <div class="sidebar-greeting">
            <p class="text-white mb-1">Selamat datang,</p>
            <h6 class="text-white fw-bold">Admin!</h6>
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

        <!-- Kelola Data -->
        <li class="menu-item {{ Request::routeIs('produk.*') || Request::routeIs('kurir.*') ? 'active' : '' }}">
            <a href="{{ route('produk.index') }}" class="menu-link">
                <i class="bi bi-database"></i>
                <span>Kelola Data</span>
            </a>
        </li>

        <!-- Laporan -->
        <li class="menu-item {{ Request::routeIs('laporan.*') ? 'active' : '' }}">
            <a href="{{ route('laporan.overview') }}" class="menu-link">
                <i class="bi bi-graph-up"></i>
                <span>Laporan</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" class="w-100">
            @csrf
            <button type="submit" class="btn btn-danger w-100 logout-btn">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </button>
        </form>
    </div>
</div>

<!-- Sidebar Overlay (Mobile) -->
<!-- <div class="sidebar-overlay" id="sidebarOverlay"></div> -->