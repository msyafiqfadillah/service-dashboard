<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Daftar Sparepart</div>
        <div class="table-actions">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="customSearchInput" placeholder="Search sparepart...">
            </div>
            <!-- <button class="btn-export">
                <i class="fa-solid fa-download"></i> Export <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem; margin-left: 2px;"></i>
            </button> -->
        </div>
    </div>

    <table id="KatalogPartList">
        <thead>
            <tr>
                <th>Part No</th>
                <th>Description</th>
                <th>Frame</th>
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

<?php $this->load->view('spareparts/component_side_drawer', array("url_target" => $data["populasi_unit_url"])); ?>

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
                { data: "partCd" },
                { data: "partDesc" },
                { data: "frame" },
                { data: "assemblySection" },
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
                {
                    data: null, 
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

    $(document).ready(function () {
        generate_katalog();

        $(document).on('click', '.btn-copy-info', function() {
            const code = $(this).attr('data-code');
            if (code) {
                navigator.clipboard.writeText(code);
                alert('Part Code ' + code + ' disalin!');
            }
        });
    });
</script>
