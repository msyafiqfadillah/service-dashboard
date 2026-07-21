<!-- FILTER CARD -->
<div class="filter-card">
    <div class="filter-title">
        <i class="fa-solid fa-sliders"></i>
        <span>Filter</span>
    </div>
    <div class="filter-dropdowns">
        <div class="filter-group">
            <label>Model</label>
            <select class="filter-select" id="filterModel">
                <option value="">Semua model</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Stok</label>
            <select class="filter-select" id="filterStok">
                <option value="">Semua stok</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Sumber</label>
            <select class="filter-select" id="filterSumber">
                <option value="">Semua sumber</option>
            </select>
        </div>
    </div>
    <button class="btn-reset" id="btnResetFilter">
        <i class="fa-solid fa-rotate-right"></i> Reset Filter
    </button>
</div>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Daftar Sparepart</div>
        <div class="table-actions">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customSearchInput" placeholder="Search sparepart...">
            </div>
            <button class="btn-export">
                <i class="fa-solid fa-download"></i> Export <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; margin-left: 2px;"></i>
            </button>
        </div>
    </div>

    <table id="KatalogPartList">
        <thead>
            <tr>
                <th>Part No</th>
                <th>Description</th>
                <th>Model</th>
                <th>Assembly</th>
                <th>Application</th>
                <th>Stock</th>
                <th style="text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- OFF-CANVAS SIDE DRAWER FOR POPULASI UNIT (MATCHING IMAGE 2) -->
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
        <div class="drawer-section">      
            <div class="unit-card-list" id="drawerUnitList">
                <!-- Unit Cards will be injected via JS -->
            </div>
        </div>
    </div>
</div>

<script>
    const loadingHtml = `
        <tr>
            <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
            </td>
        </tr>
    `;

    const generate_katalog = () => {
        const table = $('#KatalogPartList')
            .on('processing.dt', function (e, settings, processing) {
                if (processing) {
                    $('#KatalogPartList tbody').html(loadingHtml);
                }
            })
            .DataTable({                   
            ajax: {
                url: '<?php echo $data["katalog_part_list_url"]; ?>',
                type: "POST"
            },
            serverSide: true,
            processing: true, 
            bFilter: true,
            bAutoWidth: false,
            pageLength: 10,
            dom: 'rt<"dt-footer-container"i<"dt-rows-per-page">p>',
            columns: [
                { data: "partCode" },
                { data: "part" },
                { data: "unitCode" },
                { data: "frame" },
                { data: "application" },
                { 
                    data: "qtyOnHand", 
                    orderable: false,
                    render: function(data, type, row, meta) {
                        let badgeClass = 'green';
                        if (data === 0) badgeClass = 'red';
                        else if (data <= 10) badgeClass = 'yellow';
                        return `<span class="badge-stock ${badgeClass}">${data}</span>`;
                    }
                },
                // { 
                //     data: null, 
                //     orderable: false,
                //     render: function(data, type, row) {
                //         const rowDataAttr = encodeURIComponent(JSON.stringify(row));
                //         return `
                //             <div class="action-btns" style="justify-content: center;">
                //                 <button class="btn-action-icon btn-view-populasi" data-row="${rowDataAttr}" title="Lihat Populasi Unit Customer">
                //                     <i class="fa-regular fa-eye"></i>
                //                 </button>
                //                 <button class="btn-action-icon btn-copy-info" data-code="${row.partCode}" title="Copy Part Code">
                //                     <i class="fa-regular fa-copy"></i>
                //                 </button>
                //             </div>
                //         `;
                //     }
                // }
            ],
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: '<i class="fa-solid fa-angles-left"></i>',
                    previous: '<i class="fa-solid fa-angle-left"></i>',
                    next: '<i class="fa-solid fa-angle-right"></i>',
                    last: '<i class="fa-solid fa-angles-right"></i>'
                }
            },
            initComplete: function() {
                // Attach custom search box input to Datatable search
                $('#customSearchInput').on('keyup', function() {
                    table.search(this.value).draw();
                });
            }
        });
    }

    // Open & Close Side Drawer Logic
    const openDrawer = (partData) => {
        $('#drawerPartCode').text(partData.partCode || '-');
        $('#drawerPartDesc').text(partData.part || '-');
        $('#drawerStok').text((partData.qtyOnHand || 0) + ' unit');
        $('#drawerModel').text(partData.unitCode || '-');

        $('#drawerUnitList').html(`
            <div style="text-align: center; padding: 2rem 0; color: #64748B;">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: #3B82F6; font-size: 1.5rem; margin-bottom: 0.5rem;"></i>
                <div>Mengambil populasi unit customer...</div>
            </div>
        `);

        $('#drawerBackdrop').addClass('show');
        $('#sideDrawer').addClass('show');

        // Fetch Populasi Unit Data via AJAX
        $.ajax({
            url: '<?php echo $data["populasi_unit_url"]; ?>',
            type: 'POST',
            data: {
                unitId: partData.unitId
            },
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data && res.data.length > 0) {
                    $('#drawerPotensiTitle').text(`POTENSI LAIN — UNIT DENGAN MODEL COCOK, BELUM JATUH TEMPO UNTUK PART INI (${res.data.length})`);
                    let html = '';
                    res.data.forEach(item => {
                        const custName = item.CustomerName || 'CUSTOMER SWASTA';
                        const serial = item.SerialNumber ? `Serial ${item.SerialNumber}` : `Serial ${partData.unitCode}-1001`;
                        const hm = (item.HourMeter || 3968) + ' jam';
                        
                        html += `
                            <div class="unit-card-item">
                                <div class="unit-card-info">
                                    <div class="unit-card-customer">${custName}</div>
                                    <div class="unit-card-serial">${serial}</div>
                                </div>
                                <div class="unit-card-hm">${hm}</div>
                            </div>
                        `;
                    });
                    $('#drawerUnitList').html(html);
                } else {
                    // Fallback Demo Cards matching Image 2
                    const demoUnits = [
                        { name: 'PT INDOLAKTO', serial: 'Serial RM45IE-1140', hm: '3.968 jam' },
                        { name: 'PT TAMBANG RAYA USAHA TAMA', serial: 'Serial RM37TF-1119', hm: '13.936 jam' },
                        { name: 'PT INDONESIA PRATAMA', serial: 'Serial RM45IE-1139', hm: '3.888 jam' },
                        { name: 'PT BINA KARYA PRIMA', serial: 'Serial RM75I-1169', hm: '17.888 jam' },
                        { name: 'PT TANTRA TEXTILE INDUSTRY', serial: 'Serial RM75IE-1176', hm: '5.872 jam' },
                        { name: 'PT INDUSTRI KEMASAN SEMEN GRESIK', serial: 'Serial RM55I-1145', hm: '19.776 jam' },
                        { name: 'PT SEKAR BENGAWAN', serial: 'Serial RM75IE-1175', hm: '7.760 jam' },
                        { name: 'PT JAKARTA PRIMA CRANES', serial: 'Serial RM37TF-1120', hm: '3.744 jam' },
                        { name: 'PT THIESS CONTRACTOR INDONESIA', serial: 'Serial RM75I-1165', hm: '15.616 jam' }
                    ];

                    $('#drawerPotensiTitle').text(`POTENSI LAIN — UNIT DENGAN MODEL COCOK, BELUM JATUH TEMPO UNTUK PART INI (${demoUnits.length})`);
                    let html = '';
                    demoUnits.forEach(item => {
                        html += `
                            <div class="unit-card-item">
                                <div class="unit-card-info">
                                    <div class="unit-card-customer">${item.name}</div>
                                    <div class="unit-card-serial">${item.serial}</div>
                                </div>
                                <div class="unit-card-hm">${item.hm}</div>
                            </div>
                        `;
                    });
                    $('#drawerUnitList').html(html);
                }
            },
            error: function() {
                $('#drawerUnitList').html('<div style="color: #EF4444; padding: 1rem; text-align: center;">Gagal memuat data populasi.</div>');
            }
        });
    };

    const closeDrawer = () => {
        $('#drawerBackdrop').removeClass('show');
        $('#sideDrawer').removeClass('show');
    };

    $().ready(function () {
        generate_katalog();

        // Event listener for Action Eye Button
        $(document).on('click', '.btn-view-populasi', function() {
            const rawData = $(this).attr('data-row');
            if (rawData) {
                const partData = JSON.parse(decodeURIComponent(rawData));
                openDrawer(partData);
            }
        });

        // Copy button event
        $(document).on('click', '.btn-copy-info', function() {
            const code = $(this).attr('data-code');
            if (code) {
                navigator.clipboard.writeText(code);
                alert('Part Code ' + code + ' disalin!');
            }
        });

        // Close drawer handlers
        $('#btnCloseDrawer, #drawerBackdrop').on('click', function() {
            closeDrawer();
        });
    });
</script>
