<style>
    /* Custom Badges matching mockup screenshot */
    .badge-stock {
        display: inline-block;
        padding: 0.25rem 0.65rem;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        text-align: center;
        min-width: 32px;
    }
    .badge-stock.green {
        background-color: #F0FDF4;
        border: 1px solid #86EFAC;
        color: #16A34A;
    }
    .badge-stock.yellow {
        background-color: #FFFBEB;
        border: 1px solid #FCD34D;
        color: #D97706;
    }
    .badge-stock.grey {
        background-color: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #94A3B8;
    }
    
    .badge-ratio {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.78rem;
        font-weight: 700;
    }
    .badge-ratio.ratio-green {
        background-color: #F0FDF4;
        border: 1px solid #86EFAC;
        color: #16A34A;
    }
    .badge-ratio.ratio-red {
        background-color: #FEF2F2;
        border: 1px solid #FCA5A5;
        color: #DC2626;
    }
</style>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div style="display: flex; flex-direction: column; gap: 2px;">
            <div class="table-title" style="margin-bottom: 0;"><?= $page_subtitle; ?></div>
        </div>
        <div class="table-actions" style="gap: 0.75rem; flex-wrap: wrap;">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customSearchInput" placeholder="Cari part number, deskripsi, atau nama customer...">
            </div>
        </div>
    </div>

    <table id="SparepartSalesList">
        <thead>
            <tr>
                <th>Part No</th>
                <th>Deskripsi</th>
                <th style="text-align: right; width: 80px;"><?= $data["two_years_ago"]; ?></th>
                <th style="text-align: right; width: 80px;"><?= $data["one_year_ago"]; ?></th>
                <th style="text-align: right; width: 80px;"><?= $data["current_year"]; ?></th>
                <th style="text-align: right; width: 110px;">Total Terjual</th>
                <th style="text-align: center; width: 110px;">Stok Saat Ini</th>
                <th style="text-align: center; width: 180px;">Rasio Stok/Rata² Tahunan</th>
                <th style="text-align: center; width: 120px;">List Customer</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    const loadingHtml = `
        <tr>
            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
            </td>
        </tr>
    `;

    const generate_sales = () => {
        const table = $('#SparepartSalesList')
            .on('processing.dt', function (e, settings, processing) {
                if (processing) {
                    $('#SparepartSalesList tbody').html(loadingHtml);
                }
            })
            .DataTable({                   
            ajax: {
                url: '<?php echo $data["sparepart_sales"]; ?>',
                type: "POST"
            },
            serverSide: true,
            processing: true, 
            bFilter: true,
            bAutoWidth: false,
            pageLength: 10,
            dom: 'rt<"dt-footer-container"i<"dt-rows-per-page">p>',
            order: [[5, 'desc']], // Default sort by Total Sold Descending            
            columns: [
                { 
                    data: "inventoryCD",
                    render: function(data) {
                        return `<strong>${data}</strong>`;
                    }
                },
                { data: "inventoryName" },
                { 
                    data: "twoYearAgoSold",
                    className: "text-right",
                    render: function(data) {
                        return data ? parseInt(data).toLocaleString('id-ID') : 0;
                    }
                },
                { 
                    data: "oneYearAgoSold",
                    className: "text-right",
                    render: function(data) {
                        return data ? parseInt(data).toLocaleString('id-ID') : 0;
                    }
                },
                { 
                    data: "currentSold",
                    className: "text-right",
                    render: function(data) {
                        return data ? parseInt(data).toLocaleString('id-ID') : 0;
                    }
                },
                { 
                    data: "totalSold",
                    className: "text-right",
                    render: function(data) {
                        return `<strong>${data ? parseInt(data).toLocaleString('id-ID') : 0}</strong>`;
                    }
                },
                { 
                    data: "qtyOnHand",
                    className: "text-center",
                    render: function(data) {
                        if (data === null || data === undefined || parseFloat(data) <= 0) {
                            return `<span class="badge-stock grey">Tidak ada</span>`;
                        }
                        
                        let stockVal = Math.round(parseFloat(data));
                        let badgeClass = stockVal > 10 ? 'green' : 'yellow';

                        return `<span class="badge-stock ${badgeClass}">${stockVal.toLocaleString('id-ID')}</span>`;
                    }
                },
                { 
                    data: "rasioYear",
                    className: "text-center",
                    render: function(data, type, row) {
                        const rasio = parseFloat(data);
                        
                        let ratioText = rasio.toFixed(1) + 'x';
                        let colorClass = rasio >= 1.0 ? 'ratio-green' : 'ratio-red';

                        return `<span class="badge-ratio ${colorClass}">${ratioText}</span>`;
                    }
                },
                { 
                    data: null,
                    className: "text-center",
                    orderable: false,
                    render: function(data, type, row) {
                        const rowDataAttr = encodeURIComponent(JSON.stringify(row));
                        return `
                            <div class="action-btns" style="justify-content: center;">
                                <button class="btn-action-icon btn-view-customer" data-row="${rowDataAttr}" title="Lihat Rincian Customer">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                zeroRecords: "Tidak ada data yang cocok ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(disaring dari _MAX_ total entri)",
                processing: "Memuat Data..."
            }
        });

        // Search Input
        $('#customSearchInput').on('keyup', function() {
            table.search($(this).val()).draw();
        });
    };

    $(document).ready(function() {
        generate_sales();

        // Click handler to open top customer drawer
        $(document).on('click', '.btn-view-customer', function() {
            const rawData = $(this).attr('data-row');
            if (rawData) {
                const rowData = JSON.parse(decodeURIComponent(rawData));
                openCustDrawer(rowData.inventoryCD, rowData.inventoryName, rowData.totalSold, rowData.qtyOnHand);
            }
        });
    });
</script>

<?php $this->load->view('spareparts/component_customer_drawer'); ?>
