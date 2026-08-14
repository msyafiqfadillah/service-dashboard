<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Airend Rotary Catalog</div>
        <div class="table-actions" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <label for="regionFilter" style="margin-bottom: 0; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap;">Region:</label>
                <div style="width: 180px;">
                    <select id="regionFilter" class="form-control select2-filter" style="width: 100%;">
                        <option value="">Semua region</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <label for="categoryFilter" style="margin-bottom: 0; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap;">Category:</label>
                <div style="width: 220px;">
                    <select id="categoryFilter" class="form-control select2-filter" style="width: 100%;">
                        <option value="">Semua category</option>
                    </select>
                </div>
            </div>

            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="customSearchInput" placeholder="Cari Model, CCN, Size...">
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-responsive" style="border: none;">
        <table id="AirendRotaryTable" class="table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 70px;">Region</th>
                    <th style="width: 120px;">Category</th>
                    <th>Model / Application</th>
                    <th>Airend Size</th>
                    <th>Factory Rebuilt CCN</th>
                    <th>New Airend CCN</th>
                    <th>Rebuild Kit CCN</th>
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
</div>

<style>
    .select2-container--bootstrap4 .select2-selection {
        border-radius: 8px !important;
        border-color: var(--border-color, #E2E8F0) !important;
        background-color: var(--bg-input, #F8FAFC) !important;
        font-size: 0.83rem !important;
        min-height: 34px !important;
        height: 34px !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        color: var(--text-primary, #0F172A) !important;
        padding-left: 0.75rem !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
        color: var(--text-secondary, #64748B) !important;
        line-height: 32px !important;
    }
    .select2-dropdown {
        font-size: 0.83rem !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        border-color: var(--border-color, #E2E8F0) !important;
    }

    .cat-badge-light {
        display: inline-block;
        background-color: #F1F5F9;
        border: 1px solid #CBD5E1;
        color: #334155;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .apdc-pill-light {
        display: inline-block;
        background: #DCFCE7;
        color: #15803D;
        border: 1px solid #86EFAC;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 1px 5px;
        border-radius: 4px;
        letter-spacing: 0.03em;
        vertical-align: middle;
        margin-left: 4px;
    }

    .ccn-main-light {
        font-weight: 600;
        color: var(--text-primary);
        display: inline-flex;
        align-items: center;
    }

    .ccn-sub-light {
        font-size: 0.72rem;
        color: var(--text-secondary);
        margin-top: 1px;
        font-weight: 500;
    }

    .ccn-stock-text {
        font-size: 0.73rem;
        opacity: 0.6;
        margin-top: 2px;
        font-weight: bold;
    }
</style>

<script>
    $(document).ready(function() {
        const getAirendDataUrl = '<?= $data["get_airend_rotary_url"] ?>';
        const loadingHtml = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
                </td>
            </tr>
        `;

        // Populate Region and Category filter dropdowns
        const regions = <?= json_encode($data['regions'] ?? []) ?>;
        const categories = <?= json_encode($data['categories'] ?? []) ?>;

        let regionSelect = $('#regionFilter');
        regions.forEach(r => {
            if (r) {
                regionSelect.append(`<option value="${r}">${r}</option>`);
            }
        });

        let categorySelect = $('#categoryFilter');
        categories.forEach(c => {
            if (c) {
                categorySelect.append(`<option value="${c}">${c}</option>`);
            }
        });

        $('#regionFilter').select2({
            theme: 'bootstrap4',
            placeholder: 'Semua region',
            allowClear: true
        });

        $('#categoryFilter').select2({
            theme: 'bootstrap4',
            placeholder: 'Semua category',
            allowClear: true
        });

        let airendTable = $('#AirendRotaryTable')
            .on('processing.dt', function (e, settings, processing) {
                if (processing) {
                    $('#AirendRotaryTable tbody').html(loadingHtml);
                }
            })
            .DataTable({
                ajax: {
                    url: getAirendDataUrl,
                    type: 'POST',
                    data: function(d) {
                        d.region = $('#regionFilter').val();
                        d.category = $('#categoryFilter').val();
                    }
                },
                serverSide: true,
                processing: true,
                bFilter: true,
                bAutoWidth: false,
                dom: '<"dt-header-toolbar"l>rt<"dt-footer-container"ip>',
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                ordering: true,
                order: [],
                columns: [
                    { 
                        data: 'region',
                        render: function(data) {
                            return data ? $.fn.dataTable.util.escapeRegex(data).replace(/\\/g, '') : '<span style="color: var(--text-muted);">—</span>';
                        }
                    },
                    { 
                        data: 'category',
                        render: function(data) {
                            if (!data) return '<span style="color: var(--text-muted);">—</span>';
                            let cleanData = data.trim();
                            return `<span class="cat-badge-light">${cleanData}</span>`;
                        }
                    },
                    { 
                        data: 'model',
                        render: function(data) {
                            return data ? `<strong style="color: var(--text-primary);">${data.trim()}</strong>` : '<span style="color: var(--text-muted);">—</span>';
                        }
                    },
                    { 
                        data: 'identitySize',
                        render: function(data) {
                            return data ? data.trim() : '<span style="color: var(--text-muted);">—</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            let ccn = row.factoryRebuiltCcn ? row.factoryRebuiltCcn.trim() : '';
                            let backup = row.factoryRebuiltBackup ? row.factoryRebuiltBackup.trim() : '';
                            let qty = parseInt(row.frbQtyOnHand) || 0;
                            
                            if (!ccn && !backup && qty <= 0) return '<span style="color: var(--text-muted);">—</span>';
                            
                            let html = '';
                            if (ccn) {
                                html += `<div class="ccn-main-light">${ccn}`;
                                if (parseInt(row.factoryRebuiltApdc) === 1) {
                                    html += `<span class="apdc-pill-light">APDC</span>`;
                                }
                                html += `</div>`;
                            }
                            if (backup) {
                                html += `<div class="ccn-sub-light">${backup}</div>`;
                            }
                            if (qty > 0) {
                                html += `<div class="ccn-stock-text">Stok: ${qty}</div>`;
                            }
                            return html || '<span style="color: var(--text-muted);">—</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            let clean = row.newAirendCcn ? row.newAirendCcn.trim() : '';
                            let qty = parseInt(row.naQtyOnHand) || 0;

                            if (!clean && qty <= 0) return '<span style="color: var(--text-muted);">—</span>';

                            let html = '';
                            if (clean) {
                                html += `<div class="ccn-main-light">${clean}</div>`;
                            }
                            if (qty > 0) {
                                html += `<div class="ccn-stock-text">Stok: ${qty}</div>`;
                            }
                            return html || '<span style="color: var(--text-muted);">—</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            let ccn = row.rebuiltKitAirendCcn ? row.rebuiltKitAirendCcn.trim() : '';
                            let qty = parseInt(row.rkaQtyOnHand) || 0;

                            if (!ccn && qty <= 0) return '<span style="color: var(--text-muted);">—</span>';

                            let html = '';
                            if (ccn) {
                                html += `<div class="ccn-main-light">${ccn}`;
                                if (parseInt(row.rebuiltKitAirendApdc) === 1) {
                                    html += `<span class="apdc-pill-light">APDC</span>`;
                                }
                                html += `</div>`;
                            }
                            if (qty > 0) {
                                html += `<div class="ccn-stock-text">Stok: ${qty}</div>`;
                            }
                            return html || '<span style="color: var(--text-muted);">—</span>';
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
            airendTable.search(this.value).draw();
        });

        $('#regionFilter, #categoryFilter').on('change', function() {
            airendTable.ajax.reload();
        });
    });
</script>
