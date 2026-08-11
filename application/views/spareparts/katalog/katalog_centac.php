<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Centac Catalog</div>
    </div>

    <table id="KatalogCentacList">
        <thead>
            <tr>
                <th style="width: 250px;">Customer / Serial</th>
                <th style="width: 110px;">Part No</th>
                <th>Description</th>
                <th style="width: 100px; text-align: right;">Qty</th>
                <th style="width: 120px;">Reference</th>
                <th style="width: 180px;">Assembly</th>
                <th style="text-align: center; width: 120px;">Stock</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<style>
    #KatalogCentacList {
        width: 100% !important;
    }

    #KatalogCentacList tbody td.col-customer-rowspan {
        background-color: #FFFFFF !important;
        vertical-align: top !important;
        padding: 0.85rem 1rem !important;
    }

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
</style>

<script>
    const loadingHtml = `
        <tr>
            <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
            </td>
        </tr>
    `;

    const generate_katalog_centac = () => {
        const table = $('#KatalogCentacList')
            .on('processing.dt', function (e, settings, processing) {
                if (processing) {
                    $('#KatalogCentacList tbody').html(loadingHtml);
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
                    data: "customerName",
                    render: function(data, type, row) {
                        let cust = data ? data.trim() : 'N/A';
                        let frame = row.unitInventoryCd ? row.unitInventoryCd.trim() : '-';
                        let serial = row.unitSerialNumber ? row.unitSerialNumber.trim() : '-';
                        let key = `${cust}||${frame}||${serial}`;
                        return `
                            <div class="cust-info-box" data-unitkey="${key}">
                                <div style="font-weight: 800; color: var(--text-primary); margin-bottom: 2px; font-size: 0.85rem;">${cust}</div>
                                <div style="font-size: 0.72rem; color: var(--text-secondary); font-weight: 600;">Frame ${frame} · SN ${serial}</div>
                            </div>
                        `;
                    }
                },
                { 
                    data: "partInventoryCd",
                    render: function(data) {
                        return `<strong>${data || ''}</strong>`;
                    }
                },
                { 
                    data: "partDescription",
                    render: function(data) {
                        return `<div style="font-weight: 500; color: var(--text-primary);">${data ? data.trim() : '—'}</div>`;
                    }
                },
                { 
                    data: "partQty",
                    className: "text-right",
                    render: function(data, type, row) {
                        let val = parseFloat(data);
                        let uom = row.partUom ? row.partUom.trim().toUpperCase() : 'EA';
                        if (isNaN(val) || val <= 0) {
                            return `<span style="color: var(--text-muted);">- ${uom}</span>`;
                        }
                        return `<strong>${val.toFixed(1)}</strong> <span style="font-size: 0.72rem; color: var(--text-secondary);">${uom}</span>`;
                    }
                },
                { 
                    data: "reference",
                    render: function(data) {
                        return data && data.trim().length > 0 ? data.trim() : '<span style="color: var(--text-muted);">—</span>';
                    }
                },
                { 
                    data: "application",
                    render: function(data) {
                        if (!data || !data.trim()) return '<span style="color: var(--text-muted);">—</span>';
                        return `<span class="badge-cat">${data.trim()}</span>`;
                    }
                },
                { 
                    data: "qtyOnHand",
                    className: "text-center",
                    render: function(data) {
                        let qty = parseFloat(data) || 0;
                        if (qty <= 0) {
                            return `<span style="color: var(--text-muted);">—</span>`;
                        }
                        
                        let stockVal = Math.round(qty);
                        let badgeClass = stockVal > 10 ? 'green' : 'yellow';

                        return `<span class="badge-stock ${badgeClass}">${stockVal.toLocaleString('id-ID')}</span>`;
                    }
                }
            ],
            drawCallback: function(settings) {
                let lastKey = null;
                let rowspanCell = null;
                let count = 1;

                $('#KatalogCentacList tbody tr').each(function() {
                    const cell = $(this).children('td').eq(0);
                    const box = cell.find('.cust-info-box');
                    const key = box.attr('data-unitkey');

                    if (key && key === lastKey && rowspanCell) {
                        count++;
                        rowspanCell.attr('rowspan', count);
                        cell.remove();
                    } else if (key) {
                        lastKey = key;
                        rowspanCell = cell;
                        count = 1;
                        cell.addClass('col-customer-rowspan');
                    }
                });
            },
            language: {
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: '<i class="fa-solid fa-angles-left"></i>',
                    previous: '<i class="fa-solid fa-angle-left"></i>',
                    next: '<i class="fa-solid fa-angle-right"></i>',
                    last: '<i class="fa-solid fa-angles-right"></i>'
                }
            }
        });

        // Search Input
        $('#customSearchInput').on('keyup', function() {
            table.search($(this).val()).draw();
        });
    };

    $(document).ready(function() {
        generate_katalog_centac();
    });
</script>
