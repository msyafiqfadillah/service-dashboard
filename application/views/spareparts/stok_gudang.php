<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Warehouse Stock (On Hand)</div>
    </div>

    <table id="StokGudangList">
        <thead>
            <tr>
                <th>Inventory ID</th>
                <th>Description</th>
                <th>Item Type</th>
                <th>Frame</th>
                <th>Aging</th>
                <th style="text-align: center; width: 120px;">Qty On Hand</th>
                <th style="text-align: center; width: 120px;">Qty Available</th>
                <th>Pricelist</th>
                <th style="text-align: center; width: 80px;">Sales Potential</th>
                <th style="text-align: center; width: 80px;">List Customer</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php $this->load->view('spareparts/component_side_drawer', array("url_target" => $data["populasi_unit_url"])); ?>
<?php $this->load->view('spareparts/component_customer_drawer'); ?>

<!-- MODAL FOR PART DETAILS -->
<div class="modal fade" id="partDetailsModal" tabindex="-1" role="dialog" aria-labelledby="partDetailsModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden;">
            <div class="modal-header" style="background-color: var(--bg-hover, #F8FAFC); border-bottom: 1px solid var(--border-color, #E2E8F0); padding: 1rem 1.25rem;">
                <div>
                    <h5 class="modal-title" id="partDetailsModalLabel" style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary, #0F172A); margin: 0;">Daftar Frame / Model</h5>
                    <span id="modalPartCodeSub" style="font-size: 0.72rem; color: var(--text-secondary, #64748B); font-weight: 600;">-</span>
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
                        <tbody id="partDetailsTableBody">
                            <!-- Injected via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const loadingHtml = `
        <tr>
            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
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
            dom: '<"dt-header-toolbar"lf>rt<"dt-footer-container"ip>',
            lengthMenu: [10, 25, 50, 100],
            columns: [
                { 
                    data: "inventoryCD",
                    render: function(data) {
                        return `<strong>${data}</strong>`;
                    }
                },
                { data: "inventoryName" },
                { data: "itemType" },
                { 
                    data: "frame",
                    render: function(data, type, row) {
                        if (!data) return `<span style="color: var(--text-secondary); opacity: 0.6;">Tidak terpetakan ke fleet RM55-75/RM30-45</span>`;
                        
                        if (parseInt(row.frameCount) > 1) {
                            return `<span class="cell-ellipsis btn-view-part-frames" style="max-width: 320px; cursor: pointer; color: var(--accent-blue, #3B82F6); font-weight: 600;" data-part="${row.inventoryCD}" title="Klik untuk lihat semua frame">${data}...</span>`;
                        } else {
                            return `<span class="cell-ellipsis" style="max-width: 320px;" title="${data}">${data}</span>`;
                        }
                    }
                },
                { data: "aging" },
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
                    data: "qtyAvailable",
                    className: "text-center",
                    render: function(data, type, row, meta) {
                        let val = parseFloat(data) || 0;
                        let badgeClass = 'green';

                        if (val === 0) badgeClass = 'red';
                        else if (val <= 10) badgeClass = 'yellow';
                        
                        return `<span class="badge-stock ${badgeClass}">${Math.round(val)}</span>`;
                    }
                },
                { 
                    data: "salesPrice",
                    render: function (data, type, row) {
                        return currency(data);
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
                },
                { 
                    data: null,
                    className: "text-center",
                    orderable: false,
                    render: function(data, type, row) {
                        const rowDataAttr = encodeURIComponent(JSON.stringify(row));
                        return `
                           <div class="action-btns" style="justify-content: center;">
                                <button class="btn-action-icon btn-view-customer" data-row="${rowDataAttr}" title="Lihat Detail History Penjualan">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
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
        });

        // Search Input
        $('#customSearchInput').on('keyup', function() {
            table.search($(this).val()).draw();
        });
    };

    $(document).ready(function() {
        generate_stok_gudang();

        // Click handler to open part details modal
        $(document).on('click', '.btn-view-part-frames', function() {
            const partCd = $(this).attr('data-part');
            
            $('#modalPartCodeSub').text('Part No: ' + partCd);
            $('#partDetailsTableBody').html(`
                <tr>
                    <td style="text-align: center; padding: 2rem;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.5rem;"></i>
                    </td>
                </tr>
            `);

            $('#partDetailsModal').modal('show');

            $.ajax({
                url: '<?php echo site_url("spareparts/katalog_part_list/get_part_details"); ?>',
                type: 'POST',
                data: { partCd: partCd },
                dataType: 'json',
                success: function(res) {
                    if (!Array.isArray(res) || res.length === 0) {
                        $('#partDetailsTableBody').html(`
                            <tr>
                                <td style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">
                                    There are no frame details for this part.
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    // Get unique frames from the response
                    let uniqueFrames = [];
                    const framesMap = {};
                    res.forEach(item => {
                        const f = item.frame ? item.frame.trim() : '';
                        if (f && !framesMap[f]) {
                            framesMap[f] = true;
                            uniqueFrames.push(f);
                        }
                    });

                    let rowsHtml = '';
                    uniqueFrames.forEach(f => {
                        rowsHtml += `
                            <tr>
                                <td style="padding: 0.6rem 0.8rem; border-top: 1px solid var(--border-color, #E2E8F0); font-weight: 600; color: var(--text-primary, #0F172A); word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                                    ${f}
                                </td>
                            </tr>
                        `;
                    });

                    $('#partDetailsTableBody').html(rowsHtml);
                },
                error: function() {
                    $('#partDetailsTableBody').html(`
                        <tr>
                            <td style="text-align: center; padding: 1.5rem; color: #EF4444;">
                                Failed to load frame details.
                            </td>
                        </tr>
                    `);
                }
            });
        });

        // Click handler to open top customer / sales history drawer
        $(document).on('click', '.btn-view-customer', function() {
            const rawData = $(this).attr('data-row');
            if (rawData) {
                const rowData = JSON.parse(decodeURIComponent(rawData));
                openCustDrawer(rowData.inventoryCD, rowData.inventoryName, rowData.totalSold || 0, rowData.qtyOnHand);
            }
        });
    });
</script>