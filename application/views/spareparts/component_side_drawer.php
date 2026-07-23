<!-- OFF-CANVAS SIDE DRAWER FOR POPULASI UNIT -->
<div class="drawer-backdrop" id="drawerBackdrop"></div>
<div class="side-drawer" id="sideDrawer">
    <div class="drawer-header">
        <button class="btn-close-drawer" id="btnCloseDrawer"><i class="fa-solid fa-xmark"></i></button>
        <div class="drawer-sub-title">POTENSI JUAL PART</div>
        <div class="drawer-part-code" id="drawerPartCode">-</div>
        <div class="drawer-part-desc" id="drawerPartDesc">-</div>
        
        <div class="drawer-stats-row">
            <div class="drawer-stat-item">
                <span class="lbl">STOK GUDANG</span>
                <span class="val" id="drawerStok">-</span>
            </div>
            <div class="drawer-stat-item">
                <span class="lbl">MODEL COCOK</span>
                <span class="val" id="drawerModel">-</span>
            </div>
        </div>
    </div>
    
    <div class="drawer-body">        
        <!-- PELUANG AKTIF SECTION -->
        <div class="drawer-section" style="margin-bottom: 1.5rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 1.5rem;">
            <div class="drawer-section-title" id="drawerPeluangTitle" style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                PELUANG AKTIF — UNIT YANG MEMANG BUTUH PART INI DI JADWAL PM BERIKUTNYA (0)
            </div>
            <div style="font-size: 0.8rem; color: #64748B; line-height: 1.6; padding: 0.5rem 0;">
                Belum ada unit yang jadwal PM berikutnya jatuh pada part ini (bisa jadi masih di checkpoint lain dalam siklus 16.000 jam).
            </div>
        </div>

        <!-- POTENSI LAIN SECTION -->
        <div class="drawer-section">      
            <div class="drawer-section-title" id="drawerPotensiTitle" style="font-size: 0.72rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                POTENSI LAIN — UNIT DENGAN MODEL COCOK, BELUM JATUH TEMPO UNTUK PART INI (0)
            </div>
            <div class="unit-card-list" id="drawerUnitList">
                <!-- Unit Cards will be injected via JS -->
            </div>
        </div>
    </div>
</div>

<script>
    // Open & Close Side Drawer Logic
    const openDrawer = (partData) => {       
        const partCd = partData.partCd || partData.InventoryID || '-';
        const partDesc = partData.partDesc || partData.InventoryName || '-';
        const qtyOnHand = partData.qtyOnHand || 0;
        const frame = partData.frame || '-';
        const frameId = partData.frameId;
        const baseUnit = partData.baseUnit.toLowerCase();

        $('#drawerPartCode').text(partCd);
        $('#drawerPartDesc').text(partDesc);
        $('#drawerStok').text(qtyOnHand + ` ${baseUnit}`);
        $('#drawerModel').text(frame);

        $('#drawerUnitList').html(`
            <div style="text-align: center; padding: 2rem 0; color: #64748B;">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: #3B82F6; font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                <div>Mengambil populasi unit customer...</div>
            </div>
        `);

        $('#drawerBackdrop').addClass('show');
        $('#sideDrawer').addClass('show');

        if (!frameId) {
            $('#drawerPotensiTitle').text('POTENSI LAIN — UNIT DENGAN MODEL COCOK, BELUM JATUH TEMPO UNTUK PART INI (0)');
            $('#drawerUnitList').html('<div style="color: #64748B; padding: 1.5rem; text-align: center; font-size: 0.85rem;">Tidak ada unit customer yang terdaftar untuk unit ini.</div>');
            return;
        }

        // Fetch Populasi Unit Data via AJAX
        $.ajax({
            url: '<?php echo $url_target; ?>',
            type: 'POST',
            data: { frameId },
            dataType: 'json',
            success: function(res) {
                const listData = Array.isArray(res) ? res : (res && res.data ? res.data : []);
                
                if (listData.length > 0) {
                    $('#drawerPotensiTitle').text(`POTENSI LAIN — UNIT DENGAN MODEL COCOK, BELUM JATUH TEMPO UNTUK PART INI (${listData.length})`);
                    
                    let html = '';
                    listData.forEach(item => {
                        const custName = item.CustomerName || 'CUSTOMER SWASTA';
                        const custCode = item.CustomerCode || '-';
                        
                        const serialNum = item.SerialNumber && item.SerialNumber.trim().length > 0 ? `Serial ${item.SerialNumber}` : "-";
                        const rawHours = parseFloat(item.HoursMeter);
                        const hours = (!isNaN(rawHours) && rawHours > 0) ? Math.round(rawHours) : 0;
                        const serialInfo = `${serialNum} • ${item.InventoryCD}`;
                        const runningHours = `${hours.toLocaleString('id-ID')} jam`;

                        const hm = (item.BranchCD ? item.BranchCD.trim() : '-');
                        
                        html += `
                            <div class="unit-card-item">
                                <div class="unit-card-info">
                                    <div class="unit-card-customer">
                                        ${custName}
                                        <span class="unit-card-customer-code">${custCode}</span>
                                    </div>
                                    <div class="unit-card-serial">${serialInfo}</div>
                                    <div class="unit-card-running-hours">${runningHours}</div>
                                </div>
                                <div class="unit-card-hm">${hm}</div>
                            </div>
                        `;
                    });
                    
                    $('#drawerUnitList').html(html);
                } else {
                    $('#drawerPotensiTitle').text('POTENSI LAIN — UNIT DENGAN MODEL COCOK, BELUM JATUH TEMPO UNTUK PART INI (0)');
                    $('#drawerUnitList').html('<div style="color: #64748B; padding: 1.5rem; text-align: center; font-size: 0.85rem;">Tidak ada unit customer yang terdaftar untuk unit ini.</div>');
                }
            },
            error: function() {
                $('#drawerUnitList').html('<div style="color: #EF4444; padding: 1.5rem; text-align: center; font-size: 0.85rem;">Gagal memuat data populasi unit.</div>');
            }
        });
    };

    const closeDrawer = () => {
        $('#drawerBackdrop').removeClass('show');
        $('#sideDrawer').removeClass('show');
    };

    $(document).ready(function () {
        // Event listener for Action Eye Button
        $(document).on('click', '.btn-view-populasi', function() {
            const rawData = $(this).attr('data-row');
            if (rawData) {
                const partData = JSON.parse(decodeURIComponent(rawData));
                openDrawer(partData);
            }
        });

        // Close drawer handlers
        $(document).on('click', '#btnCloseDrawer, #drawerBackdrop', function() {
            closeDrawer();
        });
    });
</script>
