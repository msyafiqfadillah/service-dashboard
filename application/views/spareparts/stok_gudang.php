<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Stok Gudang (On Hand)</div>
        <div class="table-actions">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customSearchInput" placeholder="Cari inventory ID atau deskripsi...">
            </div>
            <button class="btn-export">
                <i class="fa-solid fa-download"></i> Export <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; margin-left: 2px;"></i>
            </button>
        </div>
    </div>

    <table id="StokGudangList">
        <thead>
            <tr>
                <th>Inventory ID</th>
                <th>Deskripsi</th>
                <th style="text-align: center; width: 120px;">Qty On Hand</th>
                <th>Dipakai di Model</th>
                <th style="text-align: center; width: 140px;">Potensi Jual</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php $this->load->view('spareparts/component_side_drawer', array("url_target" => $data["populasi_unit_url"])); ?>

<script>
    const loadingHtml = `
        <tr>
            <td colspan="5" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
            </td>
        </tr>
    `;

    const generate_stok_gudang = () => {
        const table = $('#StokGudangList')
            .on('processing.dt', function (e, settings, processing) {
                if (processing) {
                    $('#StokGudangList tbody').html(loadingHtml);
                }
            })
            .DataTable({                   
            ajax: {
                url: '<?php echo $data["stok_gudang_url"]; ?>',
                type: "POST"
            },
            serverSide: true,
            processing: true, 
            bFilter: true,
            bAutoWidth: false,
            pageLength: 10,
            dom: 'rt<"dt-footer-container"i<"dt-rows-per-page">p>',
            columns: [
                { 
                    data: "InventoryID",
                    render: function(data) {
                        return `<strong>${data}</strong>`;
                    }
                },
                { data: "InventoryName" },
                { 
                    data: "qtyOnHand",
                    className: "text-center",
                    render: function(data, type, row, meta) {
                        let badgeClass = 'green';

                        if (data === 0) badgeClass = 'red';
                        else if (data <= 10) badgeClass = 'yellow';
                        
                        return `<span class="badge-stock ${badgeClass}">${data}</span>`;
                    }
                },
                { 
                    data: "frame",
                    render: function(data, type, row) {
                        return data ? data : `<span style="color: var(--text-secondary); opacity: 0.6;">Tidak terpetakan ke fleet RM55-75/RM30-45</span>`;
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
                                <button class="btn-action-icon btn-view-populasi" data-row="${rowDataAttr}" title="Lihat Populasi Unit Customer">
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
        generate_stok_gudang();
    });
</script>