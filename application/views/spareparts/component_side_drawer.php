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

<!-- MODAL FOR DRAWER PART MODELS -->
<div class="modal fade" id="drawerPartModelsModal" tabindex="-1" role="dialog" aria-labelledby="drawerPartModelsModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header" style="background-color: var(--bg-hover, #F8FAFC); border-bottom: 1px solid var(--border-color, #E2E8F0); padding: 1rem 1.25rem;">
                <div>
                    <h5 class="modal-title" id="drawerPartModelsModalLabel" style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary, #0F172A); margin: 0;">Daftar Frame / Model</h5>
                    <span id="drawerModalPartCodeSub" style="font-size: 0.72rem; color: var(--text-secondary, #64748B); font-weight: 600;">-</span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 1.25rem; color: var(--text-secondary, #64748B); opacity: 0.8; outline: none; border: none; background: transparent;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 1.25rem;">
                <div style="border: 1px solid var(--border-color, #E2E8F0); border-radius: 8px; overflow: hidden; max-height: 350px; overflow-y: auto;">
                    <table class="table" style="width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 0.8rem; table-layout: fixed;">
                        <thead>
                            <tr style="background-color: var(--bg-hover, #F8FAFC); border-bottom: 1px solid var(--border-color, #E2E8F0);">
                                <th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-secondary, #64748B); border-top: none; border-bottom: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">FRAME / MODEL</th>
                            </tr>
                        </thead>
                        <tbody id="drawerPartModelsTableBody">
                            <!-- Injected dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Open & Close Side Drawer Logic
    const openDrawer = (partData) => {       
        const partCd = partData.partCd || partData.inventoryCD || '-';
        const partDesc = partData.partDesc || partData.inventoryName || '-';
        const qtyOnHand = partData.qtyOnHand || 0;
        const frame = partData.frame || '-';
        const frameId = partData.frameId;
        const baseUnit = partData.baseUnit ? partData.baseUnit.toLowerCase() : 'ea';

        $('#drawerPartCode').text(partCd);
        $('#drawerPartDesc').text(partDesc);
        $('#drawerStok').text(qtyOnHand + ` ${baseUnit}`);

        $('#drawerModel').html(`
            <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue, #3B82F6); font-size: 0.85rem;"></i>
        `);

        $.ajax({
            url: '<?php echo site_url("spareparts/katalog/katalog_part_list/get_part_details"); ?>',
            type: 'POST',
            data: { partCd: partCd },
            dataType: 'json',
            success: function(res) {
                let uniqueFrames = [];
                if (Array.isArray(res)) {
                    const framesMap = {};
                    res.forEach(item => {
                        const f = item.frame ? item.frame.trim() : '';
                        if (f && !framesMap[f]) {
                            framesMap[f] = true;
                            uniqueFrames.push(f);
                        }
                    });
                }

                if (uniqueFrames.length > 1) {
                    const encodedModels = encodeURIComponent(JSON.stringify(uniqueFrames));
                    $('#drawerModel').html(`<span class="btn-view-drawer-models" style="color: var(--accent-blue, #3B82F6); font-weight: 600; cursor: pointer; text-decoration: none;" data-part="${partCd}" data-models="${encodedModels}">${uniqueFrames[0]}...</span>`);
                } else if (uniqueFrames.length === 1) {
                    $('#drawerModel').text(uniqueFrames[0]);
                } else {
                    $('#drawerModel').text('-');
                }
            },
            error: function() {
                $('#drawerModel').text('-');
            }
        });

        $('#drawerUnitList').html(`
            <div style="text-align: center; padding: 2rem 0; color: #64748B;">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: #3B82F6; font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                <div>Mengambil populasi unit customer...</div>
            </div>
        `);

        $('#drawerBackdrop').addClass('show');
        $('#sideDrawer').addClass('show');

        if (!partCd || partCd === '-') {
            $('#drawerPotensiTitle').text('POTENSI LAIN — UNIT DENGAN MODEL COCOK, BELUM JATUH TEMPO UNTUK PART INI (0)');
            $('#drawerUnitList').html('<div style="color: #64748B; padding: 1.5rem; text-align: center; font-size: 0.85rem;">There are no registered customers for this unit.</div>');
            return;
        }

        // Fetch Populasi Unit Data via AJAX
        $.ajax({
            url: '<?php echo $url_target; ?>',
            type: 'POST',
            data: { partCd },
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
                    $('#drawerUnitList').html('<div style="color: #64748B; padding: 1.5rem; text-align: center; font-size: 0.85rem;">There are no registered customers for this unit.</div>');
                }
            },
            error: function() {
                $('#drawerUnitList').html('<div style="color: #EF4444; padding: 1.5rem; text-align: center; font-size: 0.85rem;">Failed to load unit population data.</div>');
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

        // Click handler to open drawer part models modal
        $(document).on('click', '.btn-view-drawer-models', function() {
            const partCd = $(this).attr('data-part');
            const encodedModels = $(this).attr('data-models');
            const models = JSON.parse(decodeURIComponent(encodedModels));

            $('#drawerModalPartCodeSub').text('Part No: ' + partCd);
            
            let rowsHtml = '';
            models.forEach(m => {
                rowsHtml += `
                    <tr>
                        <td style="padding: 0.6rem 0.8rem; border-top: 1px solid var(--border-color, #E2E8F0); font-weight: 600; color: var(--text-primary, #0F172A); word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                            ${m}
                        </td>
                    </tr>
                `;
            });

            $('#drawerPartModelsTableBody').html(rowsHtml);
            $('#drawerPartModelsModal').modal('show');
        });

        // Close drawer handlers
        $(document).on('click', '#btnCloseDrawer, #drawerBackdrop', function() {
            closeDrawer();
        });
    });
</script>
