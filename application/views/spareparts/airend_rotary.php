<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Katalog Airend Rotary</div>
    </div>

    <!-- TOOLBAR SEARCH & FILTERS -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 260px; max-width: 420px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="airendSearchInput" placeholder="Cari model, category, airend size, atau CCN...">
        </div>
        
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <select id="regionFilter" class="filter-select" style="width: auto; min-width: 150px;">
                <option value="">Semua region</option>
            </select>
            <select id="categoryFilter" class="filter-select" style="width: auto; min-width: 170px;">
                <option value="">Semua category</option>
            </select>
        </div>
    </div>

    <!-- INFO BANNER -->
    <div style="background-color: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; border-radius: 8px; padding: 0.85rem 1.1rem; font-size: 0.83rem; line-height: 1.5; margin-bottom: 1.25rem;">
        <i class="fa-solid fa-circle-info" style="margin-right: 6px; color: var(--accent-blue);"></i>
        Airend adalah unit rotor inti kompresor screw. Untuk tiap model, tabel menunjukkan 3 opsi penggantian: <strong>Factory Rebuilt</strong> (airend rekondisi pabrik), <strong>New</strong> (airend baru), atau <strong>Rebuild Kit</strong> (kit seal/bearing untuk rebuild airend existing) &mdash; beserta status stok APDC bila tersedia.
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
                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
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
        color: var(--text-secondary, #64748B);
        opacity: 0.6;
        margin-top: 2px;
        font-weight: 500;
    }
</style>

<script>
    $(document).ready(function() {
        const getAirendDataUrl = '<?= $data["get_airend_rotary_url"] ?>';
        const loadingHtml = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                    <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Memuat data...</div>
                </td>
            </tr>
        `;

        // Populate Region and Category filter dropdowns
        const regions = <?= json_encode($data['regions'] ?? []) ?>;
        const categories = <?= json_encode($data['categories'] ?? []) ?>;

        let regionSelect = $('#regionFilter');
        regions.forEach(r => {
            regionSelect.append(`<option value="${r}">${r}</option>`);
        });

        let categorySelect = $('#categoryFilter');
        categories.forEach(c => {
            categorySelect.append(`<option value="${c}">${c}</option>`);
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
                    type: 'POST'
                },
                serverSide: true,
                processing: true,
                bFilter: true,
                bAutoWidth: false,
                dom: 'rt<"dt-footer-container"i<"dt-rows-per-page">p>',
                pageLength: 10,
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

        // Search Input Functionality
        $('#airendSearchInput').on('keyup search input', function() {
            airendTable.search(this.value).draw();
        });

        // Filter Dropdowns Functionality
        $('#regionFilter').on('change', function() {
            let val = $.fn.dataTable.util.escapeRegex($(this).val());
            airendTable.column(0).search(val ? '^' + val + '$' : '', true, false).draw();
        });

        $('#categoryFilter').on('change', function() {
            let val = $.fn.dataTable.util.escapeRegex($(this).val());
            airendTable.column(1).search(val ? '^' + val + '$' : '', true, false).draw();
        });
    });
</script>
