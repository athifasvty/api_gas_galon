<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid">
        <!-- Toggle Sidebar Button (Mobile) -->
        <button class="btn btn-link d-lg-none" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Page Title -->
        <span class="navbar-brand mb-0 h1 ms-2">
            @yield('page-title', 'Dashboard')
        </span>

        <!-- Navbar Right -->
        <div class="ms-auto d-flex align-items-center">
            <!-- Notifications (Optional - untuk future) -->
            <div class="dropdown me-3">
                <button class="btn btn-link text-dark position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    <li><a class="dropdown-item" href="#">
                        <small class="text-muted">Pesanan baru dari John Doe</small>
                    </a></li>
                    <li><a class="dropdown-item" href="#">
                        <small class="text-muted">Stok Elpiji 3kg menipis</small>
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center small" href="#">Lihat semua</a></li>
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-dark text-decoration-none dropdown-toggle d-flex align-items-center" 
                        type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <div class="avatar-circle me-2">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="d-none d-md-inline">{{ session('auth_name', 'Admin') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <h6 class="dropdown-header">
                            <div class="fw-bold">{{ session('auth_name', 'Admin') }}</div>
                            <small class="text-muted">{{ session('auth_role', 'admin') }}</small>
                        </h6>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-gear me-2"></i> Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>