<style>
    .cell-ellipsis {
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
</style>

<!-- DATA TABLE CARD -->
<div class="table-card">
    <div class="table-header">
        <div class="table-title">Katalog Parts</div>
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
            lengthMenu: [10, 25, 50, 100],
            dom: '<"dt-header-toolbar"lf>rt<"dt-footer-container"ip>',
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
                                    Tidak ada detail frame untuk part ini.
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
                                    Tidak ada data frame.
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
                                Gagal memuat data detail frame.
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
                                    Tidak ada detail assembly untuk part ini.
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
                                    Tidak ada data assembly.
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
                                Gagal memuat data detail assembly.
                            </td>
                        </tr>
                    `);
                }
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
