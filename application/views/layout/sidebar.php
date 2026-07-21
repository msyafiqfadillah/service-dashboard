<aside class="sidebar">
    <div class="brand">
        <div class="brand-info">
            <div class="brand-logo">fmm</div>
            <div class="brand-text">
                <span class="brand-title">FMM Service Dashboard</span>
            </div>
        </div>
        <div class="brand-collapse" title="Collapse Sidebar">
            <i class="fa-solid fa-indent"></i>
        </div>
    </div>

    <div class="menu-group">
        <div class="menu-title">OVERVIEW</div>
        <ul class="menu-list">
            <li>
                <a href="<?= site_url('dashboard') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'dashboard') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-house"></i></span>
                    Dashboard
                </a>
            </li>
        </ul>
    </div>

    <div class="menu-group">
        <div class="menu-title">UNIT</div>
        <ul class="menu-list">
            <li>
                <a href="<?= site_url('dashboard/populasi') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'populasi') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-chart-simple"></i></span>
                    Populasi Unit
                </a>
            </li>
            <li>
                <a href="<?= site_url('dashboard/master') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'master') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-wrench"></i></span>
                    Master Unit
                </a>
            </li>
            <li>
                <a href="<?= site_url('dashboard/riwayat') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'riwayat') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-regular fa-file-lines"></i></span>
                    Riwayat Servis
                </a>
            </li>
        </ul>
    </div>

    <div class="menu-group">
        <div class="menu-title">SPAREPART</div>
        <ul class="menu-list">
            <li>
                <a href="<?= site_url('katalog_part_list') ?>" class="menu-item <?= (isset($active_menu) && ($active_menu == 'katalog' || $active_menu == 'katalog_part_list')) ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-cube"></i></span>
                    Katalog Parts
                </a>
            </li>
            <li>
                <a href="<?= site_url('dashboard/stok') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'stok') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-cubes"></i></span>
                    Stok Gudang
                </a>
            </li>
            <li>
                <a href="<?= site_url('dashboard/penjualan') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'penjualan') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                    Penjualan Sparepart
                </a>
            </li>
            <li>
                <a href="<?= site_url('dashboard/crossref') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'crossref') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-right-left"></i></span>
                    Cross-Reference
                </a>
            </li>
            <li>
                <a href="<?= site_url('dashboard/jadwalpm') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'jadwalpm') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-regular fa-calendar-days"></i></span>
                    Jadwal PM (CCN)
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <a href="<?= site_url('auth/logout') ?>" class="logout-item">
            <span class="menu-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></span>
            Logout
        </a>
    </div>
</aside>
