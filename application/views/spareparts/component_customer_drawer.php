<!-- OFF-CANVAS SIDE DRAWER FOR TOP CUSTOMERS -->
<div class="drawer-backdrop" id="custDrawerBackdrop"></div>
<div class="side-drawer customer-drawer" id="custSideDrawer">
    <div class="drawer-header">
        <button class="btn-close-drawer" id="btnCloseCustDrawer"><i class="fa-solid fa-xmark"></i></button>
        <div class="drawer-sub-title">RIWAYAT PENJUALAN <?= $data["two_years_ago"] ?>–<?= $data["current_year"] ?></div>
        <div class="drawer-part-code" id="custDrawerPartCode">-</div>
        <div class="drawer-part-desc" id="custDrawerPartDesc">-</div>
        
        <div class="drawer-stats-row">
            <div class="drawer-stat-item">
                <span class="lbl">TOTAL TERJUAL</span>
                <span class="val" id="custDrawerTotalSold">-</span>
            </div>
            <div class="drawer-stat-item">
                <span class="lbl">STOK SAAT INI</span>
                <span class="val" id="custDrawerStok">-</span>
            </div>
            <div class="drawer-stat-item">
                <span class="lbl">JUMLAH CUSTOMER</span>
                <span class="val" id="custDrawerCustCount">-</span>
            </div>
        </div>
    </div>
    
    <div class="drawer-body">        
        <div class="drawer-section">      
            <div class="drawer-section-title">
                Top Customer Pembeli
            </div>
            
            <table class="cust-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">Customer</th>
                        <th style="text-align: left;">Branch</th>
                        <th style="text-align: right; width: 70px;">Qty</th>
                        <th style="text-align: right; width: 110px;">Terakhir Beli</th>
                    </tr>
                </thead>
                <tbody id="custDrawerList">
                    <!-- Customer rows will be injected via JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .cust-table {
        width: 100%;
        border-collapse: collapse;
    }
    .cust-table th {
        font-size: 0.68rem;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.5rem 0;
        border-bottom: 1px solid #E2E8F0;
    }
    .cust-table td {
        font-size: 0.8rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #E2E8F0;
        color: #0F172A;
        vertical-align: middle;
    }
    .cust-table tbody tr:hover td {
        background-color: #F8FAFC;
        color: #0F172A;
    }
    .cust-table strong {
        color: #0F172A;
        font-weight: 700;
    }
</style>

<script>
    // Open Customer Side Drawer
    const openCustDrawer = (inventoryCD, inventoryName, totalSold, qtyOnHand) => {
        $('#custDrawerPartCode').text(inventoryCD);
        $('#custDrawerPartDesc').text(inventoryName);
        $('#custDrawerTotalSold').text(totalSold ? parseInt(totalSold).toLocaleString('id-ID') : 0);
        
        let stockVal = Math.round(parseFloat(qtyOnHand));
        $('#custDrawerStok').text(isNaN(stockVal) ? '0' : stockVal.toLocaleString('id-ID'));

        $('#custDrawerList').html(`
            <tr>
                <td colspan="4" style="text-align: center; padding: 3rem 0; color: #64748B;">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                    <div>Mengambil data top customer...</div>
                </td>
            </tr>
        `);

        $('#custDrawerBackdrop').addClass('show');
        $('#custSideDrawer').addClass('show');

        // Fetch Top Customer via AJAX
        $.ajax({
            url: '<?php echo site_url("spareparts/penjualan_part/get_top_customers"); ?>',
            type: 'GET',
            data: { inventoryCd: inventoryCD },
            dataType: 'json',
            success: function(res) {
                const listData = Array.isArray(res) ? res : [];
                const totalCust = listData.length;
                
                // Set customer count card value
                if (totalCust > 7) {
                    $('#custDrawerCustCount').text(`${totalCust}+`);
                } else {
                    $('#custDrawerCustCount').text(totalCust);
                }

                if (listData.length > 0) {
                    let html = '';
                    listData.forEach(item => {
                        const custName = item.customerName || '-';
                        const branchRaw = item.branchCD ? item.branchCD.trim() : '-';
                        const qtyVal = item.qty ? parseInt(item.qty).toLocaleString('id-ID') : 0;
                        const lastTranDate = item.tranDate ? item.tranDate.substring(0, 10) : '-';

                        html += `
                            <tr>
                                <td>${custName}</td>
                                <td>${branchRaw}</td>
                                <td style="text-align: right;"><strong>${qtyVal}</strong></td>
                                <td style="text-align: right; font-variant-numeric: tabular-nums;">${lastTranDate}</td>
                            </tr>
                        `;
                    });
                    $('#custDrawerList').html(html);
                } else {
                    $('#custDrawerList').html(`
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem 0; color: #64748B;">
                                Tidak ada riwayat pembelian customer.
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#custDrawerList').html(`
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem 0; color: #EF4444;">
                            Gagal memuat data top customer.
                        </td>
                    </tr>
                `);
            }
        });
    };

    $(document).ready(function() {
        // Close Cust Drawer Event handler
        $('#btnCloseCustDrawer, #custDrawerBackdrop').on('click', function() {
            $('#custDrawerBackdrop').removeClass('show');
            $('#custSideDrawer').removeClass('show');
        });
    });
</script>
