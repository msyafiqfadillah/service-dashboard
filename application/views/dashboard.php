<div class="header">
    <div class="header-title">
        <h1><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
        <p><?= isset($page_subtitle) ? $page_subtitle : 'Ringkasan data...' ?></p>
    </div>
    <div class="header-actions">
        <button class="btn" id="btnRefreshDashboard" style="background-color: transparent; border: 1px solid var(--border-color, #E2E8F0); border-radius: 6px; padding: 0.4rem 0.8rem; font-size: 0.8rem; font-weight: 600; color: var(--text-primary, #0F172A); cursor: pointer; display: flex; align-items: center; gap: 0.4rem;">
            <i class="fa-solid fa-arrows-rotate"></i> Refresh
        </button>
    </div>
</div>

<div class="content-area">
    <div class="dashboard-card-container">
        <div class="dashboard-section-title-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.85rem;">
            <h2 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary, #0F172A); margin: 0;">Distribusi Unit per Branch</h2>
            <span style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; background-color: var(--bg-hover, #F1F5F9); color: var(--text-secondary, #64748B); padding: 0.15rem 0.4rem; border-radius: 4px;">klik branch untuk detail</span>
        </div>

        <div class="branch-grid" id="branchGrid">
            <!-- Branch cards will be rendered here dynamically -->
        </div>
    </div>
</div>

<!-- SIDE DRAWER FOR BRANCH DETAIL -->
<div class="drawer-backdrop" id="branchDrawerBackdrop"></div>
<div class="side-drawer" id="branchSideDrawer">
    <div class="drawer-header" style="position: relative; padding: 1rem; border-bottom: 1px solid var(--border-color, #E2E8F0);">
        <button class="btn-close-drawer" id="btnCloseBranchDrawer" style="position: absolute; right: 1rem; top: 1rem; border: none; background: transparent; font-size: 1.1rem; color: var(--text-secondary, #64748B); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        <div class="drawer-sub-title" style="font-size: 0.65rem; font-weight: 700; color: var(--text-secondary, #64748B); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.15rem;">DETAIL BRANCH</div>
        <div class="drawer-branch-name" id="drawerBranchName" style="font-size: 1.1rem; font-weight: 800; color: var(--text-primary, #0F172A); margin-bottom: 0.1rem;">-</div>
        <div class="drawer-branch-code" id="drawerBranchCode" style="font-size: 0.75rem; color: var(--text-secondary, #64748B); font-weight: 600;">-</div>
        
        <div class="drawer-stats-row" style="display: flex; gap: 3rem; margin-top: 1rem;">
            <div class="drawer-stat-item" style="display: flex; flex-direction: column;">
                <span class="lbl" style="font-size: 0.6rem; font-weight: 700; color: var(--text-secondary, #64748B); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.1rem;">UNIT</span>
                <span class="val" id="drawerUnitCount" style="font-size: 1.1rem; font-weight: 800; color: var(--accent-blue, #3B82F6);">-</span>
            </div>
            <div class="drawer-stat-item" style="display: flex; flex-direction: column;">
                <span class="lbl" style="font-size: 0.6rem; font-weight: 700; color: var(--text-secondary, #64748B); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.1rem;">CUSTOMER</span>
                <span class="val" id="drawerCustomerCount" style="font-size: 1.1rem; font-weight: 800; color: var(--accent-blue, #3B82F6);">-</span>
            </div>
        </div>
    </div>
    
    <div class="drawer-body" style="padding: 1rem; overflow-y: auto; height: calc(100% - 110px);">        
        <div class="drawer-section">      
            <div class="unit-table-wrapper" style="border: 1px solid var(--border-color, #E2E8F0); border-radius: 6px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.01);">
                <table class="unit-table" style="width: 100%; border-collapse: collapse; text-align: left; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 25%;">
                        <col style="width: 25%;">
                        <col style="width: 50%;">
                    </colgroup>
                    <thead>
                        <tr style="background-color: var(--bg-hover, #F8FAFC); border-bottom: 1px solid var(--border-color, #E2E8F0);">
                            <th style="padding: 0.5rem 0.4rem; font-size: 0.65rem; font-weight: 700; color: var(--text-secondary, #64748B); text-transform: uppercase; letter-spacing: 0.05em;">UNIT</th>
                            <th style="padding: 0.5rem 0.4rem; font-size: 0.65rem; font-weight: 700; color: var(--text-secondary, #64748B); text-transform: uppercase; letter-spacing: 0.05em;">SERIAL</th>
                            <th style="padding: 0.5rem 0.4rem; font-size: 0.65rem; font-weight: 700; color: var(--text-secondary, #64748B); text-transform: uppercase; letter-spacing: 0.05em;">CUSTOMER</th>
                        </tr>
                    </thead>
                    <tbody id="drawerUnitTableBody">
                        <!-- Table rows will be injected via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dashboard Header & Title Layout Overrides */
    .header {
        padding: 0.65rem 0.25rem !important;
        margin-bottom: 0.65rem !important;
    }
    .header-title h1 {
        font-size: 1.1rem !important;
        font-weight: 700 !important;
        margin-bottom: 0.1rem !important;
    }
    .header-title p {
        font-size: 0.72rem !important;
        color: var(--text-secondary, #64748B) !important;
    }
    .header-actions .btn {
        padding: 0.35rem 0.7rem !important;
        font-size: 0.75rem !important;
        border-radius: 5px !important;
    }

    /* Premium Grid & Card Styling */
    .dashboard-card-container {
        background-color: var(--card-bg, #FFFFFF);
        border: 1px solid var(--border-color, #E2E8F0);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        margin-bottom: 1.25rem;
    }
    .branch-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
        gap: 0.75rem;
    }
    .branch-card {
        background-color: var(--bg-hover, #F8FAFC);
        border: 1px solid var(--border-color, #E2E8F0);
        border-radius: 8px;
        padding: 0.85rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.01);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .branch-card:hover {
        background-color: var(--card-bg, #FFFFFF);
        transform: translateY(-2px);
        border-color: var(--accent-blue, #3B82F6);
        box-shadow: 0 6px 12px -3px rgba(59, 130, 246, 0.08), 0 3px 5px -1px rgba(59, 130, 246, 0.02);
    }
    .branch-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 0;
        background-color: var(--accent-blue, #3B82F6);
        transition: height 0.25s ease;
    }
    .branch-card:hover::before {
        height: 100%;
    }
    .branch-card-header {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        margin-bottom: 0.65rem;
    }
    .branch-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary, #0F172A);
    }
    .branch-code {
        display: none;
    }
    .branch-card-body {
        margin-bottom: 0.5rem;
    }
    .branch-unit-count {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--accent-green, #10B981);
        line-height: 1.1;
        margin-bottom: 0.2rem;
    }
    .branch-customer-info {
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--text-secondary, #64748B);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Off-Canvas side drawer layout */
    .drawer-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 1040;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .drawer-backdrop.show {
        opacity: 1;
        visibility: visible;
    }
    .side-drawer {
        position: fixed;
        top: 0;
        right: -460px;
        width: 460px;
        height: 100vh;
        background-color: var(--card-bg, #FFFFFF);
        box-shadow: -10px 0 25px -5px rgba(0,0,0,0.1), -8px 0 10px -6px rgba(0,0,0,0.1);
        z-index: 1050;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @media (max-width: 576px) {
        .side-drawer {
            width: 100vw;
            right: -100vw;
        }
    }
    .side-drawer.show {
        right: 0;
    }

    /* Table styles */
    .unit-table th, .unit-table td {
        border-bottom: 1px solid var(--border-color, #E2E8F0);
        vertical-align: middle;
        padding: 0.5rem 0.4rem;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    .unit-table tbody tr {
        transition: background-color 0.15s ease;
    }
    .unit-table tbody tr:hover {
        background-color: var(--bg-hover, #F8FAFC);
    }
    .unit-table tbody tr:last-child td {
        border-bottom: none;
    }
    .unit-table td {
        font-size: 0.73rem;
        color: var(--text-primary, #0F172A);
    }

    /* Family Badge */
    .family-badge {
        display: inline-block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 0.65rem;
        background-color: var(--bg-hover, #F1F5F9);
        color: var(--text-secondary, #475569);
        padding: 0.15rem 0.35rem;
        border-radius: 4px;
        font-weight: 600;
        border: 1px solid var(--border-color, #E2E8F0);
        vertical-align: middle;
    }
</style>

<script>
    const branchNames = {
        '00 HO': 'Bekasi',
        '02 SBY': 'Surabaya',
        '03 MDN': 'Medan',
        '04 PLB': 'Palembang',
        '05 BPP': 'Balikpapan',
        '06 BTM': 'Batam',
        '12 SMG': 'Semarang',
        '14 MKS': 'Makassar'
    };

    const loadDashboardData = () => {
        $('#branchGrid').html(`
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem;">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 2rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary);">Memuat data distribusi unit...</div>
            </div>
        `);

        $.ajax({
            url: '<?php echo $data["get_unit_distribution_url"]; ?>',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (!Array.isArray(res) || res.length === 0) {
                    $('#branchGrid').html(`
                        <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; color: var(--text-secondary);">
                            Tidak ada data distribusi unit.
                        </div>
                    `);
                    return;
                }

                let html = '';
                res.forEach(item => {
                    const code = item.BranchCD ? item.BranchCD.trim() : '-';
                    const name = branchNames[code] || code;
                    const unitsCount = parseInt(item.CountSerialNumber) || 0;
                    const customersCount = parseInt(item.CountCustomerCode) || 0;

                    html += `
                        <div class="branch-card" data-branch="${code}" data-name="${name}" data-units="${unitsCount}" data-customers="${customersCount}">
                            <div class="branch-card-header">
                                <span class="branch-name">${name}</span>
                            </div>
                            <div class="branch-card-body">
                                <div class="branch-unit-count">${unitsCount}</div>
                                <div class="branch-customer-info">UNIT • ${customersCount} CUSTOMER</div>
                            </div>
                        </div>
                    `;
                });

                $('#branchGrid').html(html);
            },
            error: function () {
                $('#branchGrid').html(`
                    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; color: #EF4444;">
                        Gagal memuat data. Silakan klik tombol Refresh.
                    </div>
                `);
            }
        });
    };

    const openBranchDrawer = (branchCode, branchName, units, customers) => {
        $('#drawerBranchName').text(branchName);
        $('#drawerBranchCode').text(branchCode);
        $('#drawerUnitCount').text(units || '0');
        $('#drawerCustomerCount').text(customers || '0');

        $('#drawerUnitTableBody').html(`
            <tr>
                <td colspan="3" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                    <div style="font-size: 0.8rem; font-weight: 500;">Mengambil detail unit...</div>
                </td>
            </tr>
        `);

        $('#branchDrawerBackdrop').addClass('show');
        $('#branchSideDrawer').addClass('show');

        $.ajax({
            url: '<?php echo $data["get_branch_details_url"]; ?>',
            type: 'GET',
            data: { branch: branchCode },
            dataType: 'json',
            success: function (res) {
                if (!Array.isArray(res) || res.length === 0) {
                    $('#drawerUnitTableBody').html(`
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                                Tidak ada data unit untuk branch ini.
                            </td>
                        </tr>
                    `);
                    return;
                }

                let rowsHtml = '';
                res.forEach(item => {
                    const custName = item.CustomerName || '-';
                    const serial = item.SerialNumber || '-';
                    const invCd = item.InventoryCD || '-';

                    rowsHtml += `
                        <tr>
                            <td style="font-weight: 500;">${invCd}</td>
                            <td style="font-variant-numeric: tabular-nums;">${serial}</td>
                            <td style="font-weight: 600;">${custName}</td>
                        </tr>
                    `;
                });

                $('#drawerUnitTableBody').html(rowsHtml);
            },
            error: function () {
                $('#drawerUnitTableBody').html(`
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 3rem 1rem; color: #EF4444;">
                            Gagal memuat detail data unit.
                        </td>
                    </tr>
                `);
            }
        });
    };

    const closeBranchDrawer = () => {
        $('#branchDrawerBackdrop').removeClass('show');
        $('#branchSideDrawer').removeClass('show');
    };

    $(document).ready(function () {
        // Initial load
        loadDashboardData();

        // Refresh action
        $('#btnRefreshDashboard').on('click', function () {
            loadDashboardData();
        });

        // Click card to open drawer
        $(document).on('click', '.branch-card', function () {
            const code = $(this).attr('data-branch');
            const name = $(this).attr('data-name');
            const units = $(this).attr('data-units');
            const customers = $(this).attr('data-customers');
            openBranchDrawer(code, name, units, customers);
        });

        // Close drawer events
        $(document).on('click', '#btnCloseBranchDrawer, #branchDrawerBackdrop', function () {
            closeBranchDrawer();
        });
    });
</script>
