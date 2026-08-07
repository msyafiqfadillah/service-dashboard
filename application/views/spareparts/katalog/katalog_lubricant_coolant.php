<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Lubricant & Coolant Catalog</div>
    </div>

    <table id="KatalogLubricantList">
        <thead>
            <tr>
                <th style="width: 110px;">CCN</th>
                <th style="width: 180px;">Frame</th>
                <th>Product</th>
                <th style="width: 220px;">Category</th>
                <th style="width: 140px;">Packaging</th>
                <th style="text-align: center; width: 120px;">Warehouse Stock</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- MODAL FOR FRAME DETAILS -->
<div class="modal fade" id="partDetailsModal" tabindex="-1" role="dialog" aria-labelledby="partDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;">
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

<style>
    .badge-cat {
        display: inline-block;
        background-color: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #334155;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        white-space: nowrap;
    }
    .cell-ellipsis {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
</style>

<script>
    const loadingHtml = `
        <tr>
            <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
            </td>
        </tr>
    `;

    const generate_lubricant_coolant = () => {
        const table = $('#KatalogLubricantList')
            .on('processing.dt', function (e, settings, processing) {
                if (processing) {
                    $('#KatalogLubricantList tbody').html(loadingHtml);
                }
            })
            .DataTable({                   
            ajax: {
                url: '<?php echo $data["get_data_url"]; ?>',
                type: "POST"
            },
            serverSide: true,
            processing: true, 
            bFilter: true,
            bAutoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            dom: '<"dt-header-toolbar"lf>rt<"dt-footer-container"ip>',
            order: [[0, 'asc']],
            columns: [
                { 
                    data: "ccn",
                    render: function(data) {
                        return `<strong>${data || ''}</strong>`;
                    }
                },
                { 
                    data: "frame",
                    render: function(data, type, row) {
                        let frameCount = parseInt(row.frameCount) || 0;
                        if (frameCount > 1) {
                            let displayStr = data ? data.split(',')[0] : 'Frame List';
                            return `<span class="cell-ellipsis btn-view-lubricant-frames" style="max-width: 180px; cursor: pointer; color: var(--accent-blue, #3B82F6); font-weight: 600;" data-ccn="${row.ccn}" title="Klik untuk lihat semua frame">${displayStr}...</span>`;
                        }
                        if (frameCount === 1 && data) {
                            return `<span class="cell-ellipsis" style="max-width: 180px;" title="${data}">${data}</span>`;
                        }
                        return `<span style="color: var(--text-muted);">—</span>`;
                    }
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        let desc = row.description ? row.description.trim() : '—';
                        let app = row.applicationUsed ? row.applicationUsed.trim() : '';
                        let serviceLife = row.serviceLife ? row.serviceLife.trim() : '';
                        let isovg = row.isovg ? row.isovg.trim() : '';

                        let html = `<div style="font-weight: 700; color: var(--text-primary); margin-bottom: 2px;">${desc}</div>`;
                        if (app) {
                            html += `<div style="font-size: 0.78rem; color: var(--text-secondary); margin-bottom: 2px; line-height: 1.35;">${app}</div>`;
                        }

                        let metaParts = [];
                        if (serviceLife) metaParts.push(`Service life: ${serviceLife}`);
                        if (isovg) metaParts.push(`ISO VG ${isovg}`);
                        if (metaParts.length > 0) {
                            html += `<div style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">${metaParts.join(' · ')}</div>`;
                        }

                        return html;
                    }
                },
                { 
                    data: "category",
                    render: function(data) {
                        if (!data) return '<span style="color: var(--text-muted);">—</span>';
                        return `<span class="badge-cat">${data.trim()}</span>`;
                    }
                },
                { 
                    data: null,
                    render: function(data, type, row) {
                        let typeStr = row.containerType ? row.containerType.trim() : '';
                        let sizeStr = row.containerSize ? row.containerSize.trim() : '';
                        let combined = `${typeStr} ${sizeStr}`.trim();
                        return combined || '<span style="color: var(--text-muted);">—</span>';
                    }
                },
                { 
                    data: "qtyOnHand",
                    className: "text-center",
                    render: function(data) {
                        let qty = parseFloat(data) || 0;
                        if (qty <= 0) {
                            return `<span class="badge-stock grey">Empty</span>`;
                        }
                        
                        let stockVal = Math.round(qty);
                        let badgeClass = stockVal > 10 ? 'green' : 'yellow';

                        return `<span class="badge-stock ${badgeClass}">${stockVal.toLocaleString('id-ID')}</span>`;
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
        generate_lubricant_coolant();

        // Show Frames Modal Details
        $(document).on('click', '.btn-view-lubricant-frames', function() {
            const ccn = $(this).attr('data-ccn');
            if (!ccn) return;

            $('#partDetailsModalLabel').text('Daftar Frame / Model');
            $('#modalPartCodeSub').text('CCN: ' + ccn);
            $('#partDetailsTableBody').html(`
                <tr>
                    <td style="text-align: center; padding: 2rem;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.5rem;"></i>
                    </td>
                </tr>
            `);

            $('#partDetailsModal').modal('show');

            $.ajax({
                url: '<?php echo $data["get_lubricant_details_url"]; ?>',
                type: 'POST',
                data: { ccn: ccn },
                dataType: 'json',
                success: function(res) {
                    if (!Array.isArray(res) || res.length === 0) {
                        $('#partDetailsTableBody').html(`
                            <tr>
                                <td style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">
                                    There are no frame details for this product.
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    const uniqueFrames = [...new Set(res.map(item => item.frame).filter(Boolean))];
                    
                    if (uniqueFrames.length === 0) {
                        $('#partDetailsTableBody').html(`
                            <tr>
                                <td style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">
                                    There is no data frame.
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    let rows = '';
                    uniqueFrames.forEach(f => {
                        rows += `
                            <tr>
                                <td style="padding: 0.6rem 0.8rem; font-weight: 500; color: var(--text-primary); border-top: 1px solid var(--border-color); word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">${f}</td>
                            </tr>
                        `;
                    });

                    $('#partDetailsTableBody').html(rows);
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
    });
</script>
