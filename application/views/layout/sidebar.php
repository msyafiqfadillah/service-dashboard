<aside class="sidebar">
    <div class="brand">
        <a href="https://eps.fajarmasmurni.com/dasbor" class="brand-info" style="text-decoration: none; color: inherit;">
            <img class="brand-logo-img" src="<?php echo base_url(); ?>assets/images/logo.png" alt="FMM Logo" />
            <div class="brand-text">
                <span class="brand-title">FMM Population Unit & Part</span>
            </div>
        </a>
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
        <div class="menu-title">SPAREPARTS</div>
        <ul class="menu-list">
            <li>
                <a href="<?= site_url('spareparts/katalog_part_list') ?>" class="menu-item <?= (isset($active_menu) &&  $active_menu == 'spareparts/katalog_part_list') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-cube"></i></span>
                    Katalog Parts
                </a>
            </li>
            <li>
                <a href="<?= site_url('spareparts/stok_gudang') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'spareparts/stok_gudang') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-cubes"></i></span>
                    Stok Gudang
                </a>
            </li>
            <li>
                <a href="<?= site_url('spareparts/penjualan_part') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'spareparts/penjualan_part') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-cart-shopping"></i></span>
                    Penjualan Sparepart
                </a>
            </li>
            <li>
                <a href="<?= site_url('spareparts/cross_reference') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'spareparts/cross_reference') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-right-left"></i></span>
                    Cross-Reference
                </a>
            </li>
            <li>
                <a href="<?= site_url('spareparts/airend_rotary') ?>" class="menu-item <?= (isset($active_menu) && $active_menu == 'spareparts/airend_rotary') ? 'active' : '' ?>">
                    <span class="menu-icon"><i class="fa-solid fa-rotate"></i></span>
                    Airend Rotary
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
        <?php
            $initials = '';
            $employee_initial = $this->session->userdata('employee_initial');
            $userEmail = $this->session->userdata('email') ?? 'user@fajarmasmurni.com';
            
            if (!empty($employee_initial)) {
                $initials = strtoupper(substr($employee_initial, 0, 2));
            } else {
                if (!empty($userEmail)) {
                    $parts = explode('@', $userEmail);
                    $namePart = $parts[0];
                    $subParts = explode('.', $namePart);
                    if (count($subParts) >= 2) {
                        $initials = strtoupper(substr($subParts[0], 0, 1) . substr($subParts[1], 0, 1));
                    } else {
                        $initials = strtoupper(substr($namePart, 0, 2));
                    }
                } else {
                    $initials = 'US';
                }
            }
        ?>
        <div class="sidebar-profile-card">
            <div class="sidebar-profile-left">
                <div class="avatar-wrapper">
                    <div class="sidebar-avatar" title="<?= htmlspecialchars($userEmail) ?>">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <div class="avatar-status"></div>
                </div>
                <div class="sidebar-user-info">
                    <span class="sidebar-user-name" title="<?= htmlspecialchars($userEmail) ?>"><?= htmlspecialchars($userEmail) ?></span>
                </div>
            </div>
            <a href="<?= site_url('auth/logout') ?>" class="sidebar-logout" title="Logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </div>
</aside>

<style>
    /* Profile Card CSS */
    .sidebar-footer {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color, #E2E8F0);
    }
    .sidebar-profile-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 1px solid var(--border-color, #E2E8F0);
        border-radius: 12px;
        padding: 0.65rem 0.8rem;
        gap: 0.65rem;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .sidebar-profile-card:hover {
        border-color: #CBD5E1;
        background: linear-gradient(135deg, #F1F5F9 0%, #E2E8F0 100%);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }
    .sidebar-profile-left {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex: 1;
        min-width: 0;
    }
    .avatar-wrapper {
        position: relative;
        flex-shrink: 0;
    }
    .sidebar-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
        color: #FFFFFF !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        border: 2px solid #FFFFFF;
        text-transform: uppercase;
    }
    .avatar-status {
        position: absolute;
        bottom: -1px;
        right: -1px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #10B981;
        border: 2px solid #FFFFFF;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        animation: statusPulse 2s infinite ease-in-out;
    }
    @keyframes statusPulse {
        0% {
            box-shadow: 0 0 0 0px rgba(16, 185, 129, 0.5);
        }
        70% {
            box-shadow: 0 0 0 5px rgba(16, 185, 129, 0);
        }
        100% {
            box-shadow: 0 0 0 0px rgba(16, 185, 129, 0);
        }
    }
    .sidebar-user-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .sidebar-user-name {
        font-size: 0.8rem;
        font-weight: 700;
        color: #1E293B !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }
    .sidebar-user-role {
        font-size: 0.68rem;
        font-weight: 500;
        color: #64748B !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
        margin-top: 1px;
    }
    .sidebar-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        color: #64748B !important;
        background-color: #FFFFFF;
        border: 1px solid var(--border-color, #E2E8F0);
        transition: all 0.2s ease;
        flex-shrink: 0;
        text-decoration: none !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .sidebar-logout:hover {
        color: #EF4444 !important;
        border-color: #FCA5A5;
        background-color: #FEF2F2;
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.08);
    }
</style>
