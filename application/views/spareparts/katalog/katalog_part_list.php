<style>
    .cell-ellipsis {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
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
</style>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Parts Catalog</div>
        <div class="table-actions" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <label for="filterYear" style="margin-bottom: 0; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap;">Tahun:</label>
                <div style="width: 110px;">
                    <select id="filterYear" class="form-control select2-filter" style="width: 100%;">
                        <?php 
                        $currYear = date('Y');
                        for ($y = $currYear; $y >= $currYear - 5; $y--): 
                        ?>
                            <option value="<?= $y ?>" <?= $y == $currYear ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <label for="filterModel" style="margin-bottom: 0; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap;">Model:</label>
                <div style="width: 220px;">
                    <select id="filterModel" class="form-control select2-filter" style="width: 100%;">
                        <option value="">Semua Model</option>
                        <?php if (isset($data['frames']) && !empty($data['frames'])): ?>
                            <?php foreach ($data['frames'] as $f): ?>
                                <option value="<?= htmlspecialchars($f['frame']) ?>"><?= htmlspecialchars($f['frame']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 0.4rem;">
                <label for="filterStock" style="margin-bottom: 0; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); white-space: nowrap;">Stock:</label>
                <div style="width: 160px;">
                    <select id="filterStock" class="form-control select2-filter" style="width: 100%;">
                        <option value="">Semua Stok</option>
                        <option value="ready">Ready Stock (> 0)</option>
                        <option value="empty">Stok Kosong (= 0)</option>
                    </select>
                </div>
            </div>

            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="customSearchInput" placeholder="Cari Part No, Deskripsi...">
            </div>
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
                <th style="text-align: center; width: 90px;">Stock</th>
                <th style="text-align: center; width: 90px;">Stock Avail</th>
                <th style="text-align: center; width: 110px;">Total Penawaran</th>
                <th style="text-align: right; width: 140px;">Nilai Penawaran</th>
                <th style="text-align: center;">Action</th>
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

<!-- MODAL FOR PART DETAILS -->
<div class="modal fade" id="partDetailsModal" tabindex="-1" role="dialog" aria-labelledby="partDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 400px;">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header" style="background-color: var(--bg-hover, #F8FAFC); border-bottom: 1px solid var(--border-color, #E2E8F0); padding: 1rem 1.25rem;">
                <div>
                    <h5 class="modal-title" id="partDetailsModalLabel" style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary, #0F172A); margin: 0;">Detail</h5>
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
                            <tr style="background-color: var(--bg-hover, #F8FAFC); border-bottom: 1px solid var(--border-color, #E2E8F0);" id="modalTableHeaderRow">
                                <!-- Injected dynamically -->
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

<!-- MODAL FOR QUOTATION DETAILS -->
<div class="modal fade" id="quotationDetailsModal" tabindex="-1" role="dialog" aria-labelledby="quotationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-width: 1150px;">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="modal-header" style="background-color: var(--bg-hover, #F8FAFC); border-bottom: 1px solid var(--border-color, #E2E8F0); padding: 1rem 1.25rem;">
                <div>
                    <h5 class="modal-title" id="quotationDetailsModalLabel" style="font-size: 1rem; font-weight: 700; color: var(--text-primary, #0F172A); margin: 0;">Detail Quotation Penawaran</h5>
                    <span id="modalQuotationPartCodeSub" style="font-size: 0.8rem; color: var(--text-secondary, #64748B); font-weight: 600;">-</span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 1.25rem; color: var(--text-secondary, #64748B); opacity: 0.8; outline: none; border: none; background: transparent;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 1.25rem; overflow-x: auto;">
                <table id="tableQuotationDetails" class="table table-striped table-bordered" style="width: 100%; font-size: 0.8rem;">
                    <thead>
                        <tr style="background-color: var(--bg-hover, #F8FAFC);">
                            <th>No. Quotation</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Customer</th>
                            <th>Sales/Employee</th>
                            <th>Branch</th>
                            <th>Group</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Harga Satuan</th>
                            <th style="text-align: right;">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX Data -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('spareparts/component_side_drawer', array("url_target" => $data["populasi_unit_url"])); ?>

<script>
    const loadingHtml = `
        <tr>
            <td colspan="10" style="text-align: center; padding: 3rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.75rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-primary);">Loading data...</div>
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
                type: "POST",
                data: function(d) {
                    d.frame = $('#filterModel').val();
                    d.stockStatus = $('#filterStock').val();
                    d.year = $('#filterYear').val();
                }
            },
            serverSide: true,
            processing: true, 
            bFilter: true,
            bAutoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            dom: '<"dt-header-toolbar"l>rt<"dt-footer-container"ip>',
            columns: [
                { data: "partCd" },
                { 
                    data: "partDesc",
                    render: function(data) {
                        return `<span class="cell-ellipsis" style="max-width: 180px;" title="${data || ''}">${data || ''}</span>`;
                    }
                },
                { 
                    data: "frame",
                    render: function(data, type, row) {
                        if (row.frameCount > 1) {
                            return `<span class="cell-ellipsis btn-view-part-frames" style="max-width: 250px; cursor: pointer; color: var(--accent-blue, #3B82F6); font-weight: 600;" data-part="${row.partCd}" title="Klik untuk lihat semua frame">${data || ''}...</span>`;
                        }
                        return `<span class="cell-ellipsis" style="max-width: 250px;" title="${data || ''}">${data || ''}</span>`;
                    }
                },
                { 
                    data: "assemblySection",
                    render: function(data, type, row) {
                        if (row.assemblyCount > 1) {
                            return `<span class="cell-ellipsis btn-view-part-assemblies" style="max-width: 150px; cursor: pointer; color: var(--accent-blue, #3B82F6); font-weight: 600;" data-part="${row.partCd}" title="Klik untuk lihat semua assembly">${data || ''}...</span>`;
                        }
                        return `<span class="cell-ellipsis" style="max-width: 150px;" title="${data || ''}">${data || ''}</span>`;
                    }
                },
                { 
                    data: "application",
                    render: function(data) {
                        return `<span class="cell-ellipsis" style="max-width: 150px;" title="${data || ''}">${data || ''}</span>`;
                    }
                },
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
                    data: "TotalPenawaranEPS", 
                    className: "text-center",
                    render: function(data, type, row) {
                        let val = parseInt(data) || 0;
                        if (val > 0) {
                            return `<span class="badge-stock btn-view-quotation-details" data-part="${row.partCd}" style="background-color: #EFF6FF; border: 1px solid #93C5FD; color: #1D4ED8; cursor: pointer;" title="Klik untuk lihat detail quotation">${val}</span>`;
                        }
                        return `<span style="color: var(--text-secondary); font-size: 0.8rem;">0</span>`;
                    }
                },
                { 
                    data: "TotalPenawaranPrice", 
                    className: "text-right",
                    render: function(data) {
                        let val = parseFloat(data) || 0;
                        if (val > 0) {
                            return `<span style="font-weight: 600; color: var(--text-primary);">${new Intl.NumberFormat('id-ID').format(val)}</span>`;
                        }
                        return `<span style="color: var(--text-secondary); font-size: 0.8rem;">-</span>`;
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
        $('#filterYear').select2({
            theme: 'bootstrap4',
            minimumResultsForSearch: Infinity
        });

        $('#filterModel').select2({
            theme: 'bootstrap4',
            placeholder: 'Semua Model',
            allowClear: true
        });

        $('#filterStock').select2({
            theme: 'bootstrap4',
            placeholder: 'Semua Stok',
            allowClear: true,
            minimumResultsForSearch: Infinity
        });

        generate_katalog();

        $('#filterYear, #filterModel, #filterStock').on('change', function() {
            $('#KatalogPartList').DataTable().ajax.reload();
        });

        // Show Frames Modal Details
        $(document).on('click', '.btn-view-part-frames', function() {
            const partCd = $(this).attr('data-part');
            if (!partCd) return;

            // Set dynamic max-width for 1 column
            $('#partDetailsModal .modal-dialog').css('max-width', '400px');

            $('#partDetailsModalLabel').text('Daftar Frame / Model');
            $('#modalPartCodeSub').text('Part No: ' + partCd);
            $('#modalTableHeaderRow').html('<th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-secondary, #64748B); border-top: none; border-bottom: none; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">FRAME / MODEL</th>');
            $('#partDetailsTableBody').html(`
                <tr>
                    <td style="text-align: center; padding: 2rem;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.5rem;"></i>
                    </td>
                </tr>
            `);

            $('#partDetailsModal').modal('show');

            $.ajax({
                url: '<?php echo $data["get_part_details_url"]; ?>',
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

                    // Extract unique frames
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
                    uniqueFrames.forEach(frame => {
                        rows += `
                            <tr>
                                <td style="padding: 0.6rem 0.8rem; font-weight: 500; color: var(--text-primary); border-top: 1px solid var(--border-color); word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">${frame}</td>
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

        // Show Assembly Modal Details
        $(document).on('click', '.btn-view-part-assemblies', function() {
            const partCd = $(this).attr('data-part');
            if (!partCd) return;

            // Set dynamic max-width for 2 columns
            $('#partDetailsModal .modal-dialog').css('max-width', '600px');

            $('#partDetailsModalLabel').text('Daftar Assembly Section');
            $('#modalPartCodeSub').text('Part No: ' + partCd);
            $('#modalTableHeaderRow').html(`
                <th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-secondary, #64748B); border-top: none; border-bottom: none; width: 40%; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">FRAME</th>
                <th style="padding: 0.6rem 0.8rem; font-weight: 700; color: var(--text-secondary, #64748B); border-top: none; border-bottom: none; width: 60%; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">ASSEMBLY SECTION</th>
            `);
            $('#partDetailsTableBody').html(`
                <tr>
                    <td colspan="2" style="text-align: center; padding: 2rem;">
                        <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 1.5rem;"></i>
                    </td>
                </tr>
            `);

            $('#partDetailsModal').modal('show');

            $.ajax({
                url: '<?php echo $data["get_part_details_url"]; ?>',
                type: 'POST',
                data: { partCd: partCd },
                dataType: 'json',
                success: function(res) {
                    if (!Array.isArray(res) || res.length === 0) {
                        $('#partDetailsTableBody').html(`
                            <tr>
                                <td colspan="2" style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">
                                    There are no assembly details for this part.
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    // Extract unique combinations where assemblySection is present
                    const uniqueCombinations = [];
                    const seen = new Set();
                    res.forEach(item => {
                        if (item.assemblySection) {
                            const key = `${item.frame || ''}||${item.assemblySection || ''}`;
                            if (!seen.has(key)) {
                                seen.add(key);
                                uniqueCombinations.push(item);
                            }
                        }
                    });
                    
                    if (uniqueCombinations.length === 0) {
                        $('#partDetailsTableBody').html(`
                            <tr>
                                <td colspan="2" style="text-align: center; padding: 1.5rem; color: var(--text-secondary);">
                                    There is no assembly data.
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    let rows = '';
                    uniqueCombinations.forEach(item => {
                        rows += `
                            <tr>
                                <td style="padding: 0.6rem 0.8rem; font-weight: 500; color: var(--text-primary); border-top: 1px solid var(--border-color); word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">${item.frame || '-'}</td>
                                <td style="padding: 0.6rem 0.8rem; color: var(--text-secondary); border-top: 1px solid var(--border-color); word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">${item.assemblySection || '-'}</td>
                            </tr>
                        `;
                    });
                    $('#partDetailsTableBody').html(rows);
                },
                error: function() {
                    $('#partDetailsTableBody').html(`
                        <tr>
                            <td style="text-align: center; padding: 1.5rem; color: #EF4444;">
                                Failed to load assembly details.
                            </td>
                        </tr>
                    `);
                }
            });
        });

        let quotationTable = null;

        $(document).on('click', '.btn-view-quotation-details', function() {
            const partCd = $(this).attr('data-part');
            const year = $('#filterYear').val();
            if (!partCd) return;

            $('#modalQuotationPartCodeSub').text('Part No: ' + partCd + ' (Tahun ' + year + ')');
            $('#quotationDetailsModal').modal('show');

            if ($.fn.DataTable.isDataTable('#tableQuotationDetails')) {
                $('#tableQuotationDetails').DataTable().destroy();
            }

            quotationTable = $('#tableQuotationDetails').DataTable({
                ajax: {
                    url: '<?php echo $data["get_quotation_details_url"]; ?>',
                    type: 'POST',
                    data: { partCd: partCd, year: year },
                    dataSrc: ''
                },
                bFilter: true,
                autoWidth: false,
                pageLength: 10,
                lengthMenu: [10, 25, 50],
                order: [[1, 'desc']],
                dom: '<"dt-header-toolbar"lf>rt<"dt-footer-container"ip>',
                columns: [
                    { 
                        data: "quotation_no_manual",
                        render: function(data) {
                            return `<span style="font-weight: 600; color: var(--accent-blue, #3B82F6);">${data || '-'}</span>`;
                        }
                    },
                    { 
                        data: "quotation_date",
                        render: function(data) {
                            if (!data) return '-';
                            return data.split(' ')[0];
                        }
                    },
                    { 
                        data: "quotation_status_code",
                        render: function(data) {
                            return `<span class="badge badge-info" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; font-weight: 600;">${data || '-'}</span>`;
                        }
                    },
                    { data: "customer_name", defaultContent: "-" },
                    { data: "employee_name", defaultContent: "-" },
                    { data: "branch_initial", defaultContent: "-" },
                    { data: "group_initial", defaultContent: "-" },
                    { 
                        data: "qty", 
                        className: "text-center",
                        render: function(data) {
                            return parseInt(data) || 0;
                        }
                    },
                    { 
                        data: "quotation_price", 
                        className: "text-right",
                        render: function(data) {
                            let val = parseFloat(data) || 0;
                            return new Intl.NumberFormat('id-ID').format(val);
                        }
                    },
                    { 
                        data: "total_amount", 
                        className: "text-right",
                        render: function(data) {
                            let val = parseFloat(data) || 0;
                            return `<span style="font-weight: 600; color: var(--text-primary);">${new Intl.NumberFormat('id-ID').format(val)}</span>`;
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
        });

        $(document).on('click', '.btn-copy-info', function() {
            const code = $(this).attr('data-code');
            if (code) {
                navigator.clipboard.writeText(code);
                alert('Part Code ' + code + ' disalin!');
            }
        });
    });
</script>
