<div class="xr-container">
    <div style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 0.5rem;">
        <div class="xr-title">Cross-Reference Part → Model → Customer</div>
        <div class="xr-subtitle">Search for 1 part number, find all customers who might need that part</div>
    </div>
    <div class="xr-search-card" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div class="xr-search-box" style="flex: 1; max-width: 420px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="partSearchInput" placeholder="Type in the part number or a descriptive keyword..." autofocus>
        </div>
        <div class="stock-toggle-container">
            <button class="btn-toggle active" data-filter="all">All</button>
            <button class="btn-toggle" data-filter="instock">In stock</button>
        </div>
    </div>
    <div id="resultContainer">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 1rem; gap: 1.25rem; animation: fadeIn 0.3s ease-in-out;">
            <img src="<?= base_url() ?>assets/images/undraw_file-searching_yska.svg" alt="Mulai mencari" style="width: 220px; height: auto; max-width: 100%; opacity: 0.85;">
            <div class="xr-subtitle" style="text-align: center; color: var(--text-secondary); font-size: 0.85rem; font-weight: 500; line-height: 1.5;">
                Type at least 2 characters to start your search.
            </div>
        </div>
    </div>
</div>

<!-- Modal for displaying all models -->
<div class="modal fade" id="modelsModal" tabindex="-1" role="dialog" aria-labelledby="modelsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color, #E2E8F0); padding: 1rem 1.25rem;">
                <h5 class="modal-title" id="modelsModalLabel" style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary, #0F172A);">
                    List of Engine Models
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none; border: none; background: transparent; font-size: 1.5rem; line-height: 1; padding: 0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 1.25rem; max-height: 400px; overflow-y: auto;">
                <div style="margin-bottom: 0.75rem; font-size: 0.85rem; color: var(--text-secondary, #64748B);">
                    <span id="modalPartLabel">Part Number: </span><strong id="modalPartNumber" style="color: var(--text-primary, #0F172A);"></strong>
                </div>
                <div id="modalModelsContainer" style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                    <!-- Models tags will be generated here -->
                </div>
            </div>
            <div class="modal-body-footer" style="border-top: 1px solid var(--border-color, #E2E8F0); padding: 0.75rem 1.25rem; display: flex; justify-content: flex-end; background-color: var(--bg-hover, #F8FAFC); border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn" data-dismiss="modal" style="font-size: 0.8rem; font-weight: 600; padding: 0.4rem 1rem; border-radius: 6px; background-color: var(--card-bg, #FFFFFF); border: 1px solid var(--border-color, #E2E8F0); color: var(--text-secondary, #475569); box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Compact Cross-Reference Page Styling */
    .xr-container {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        padding-bottom: 1.5rem;
    }
    .xr-search-card {
        background-color: var(--card-bg, #FFFFFF);
        border: 1px solid var(--border-color, #E2E8F0);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    .stock-toggle-container {
        display: inline-flex;
        background-color: var(--bg-hover, #F8FAFC);
        border: 1px solid var(--border-color, #E2E8F0);
        padding: 2px;
        border-radius: 6px;
    }
    .btn-toggle {
        background: transparent;
        border: none;
        outline: none;
        padding: 0.35rem 0.75rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-secondary, #64748B);
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-toggle.active {
        background-color: var(--card-bg, #FFFFFF);
        color: var(--accent-blue, #3B82F6);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    .xr-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary, #0F172A);
        margin-bottom: 0.15rem;
    }
    .xr-subtitle {
        font-size: 0.8rem;
        color: var(--text-secondary, #64748B);
    }
    .xr-search-box {
        display: flex;
        align-items: center;
        background-color: var(--bg-hover, #F8FAFC);
        border: 1px solid var(--border-color, #E2E8F0);
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        gap: 0.5rem;
        transition: all 0.2s ease;
        max-width: 420px;
    }
    .xr-search-box:focus-within {
        border-color: var(--accent-blue, #3B82F6);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        background-color: var(--card-bg, #FFFFFF);
    }
    .xr-search-box i {
        color: var(--text-secondary, #64748B);
        font-size: 0.95rem;
    }
    .xr-search-box input {
        border: none;
        background: transparent;
        color: var(--text-primary, #0F172A);
        font-size: 0.85rem;
        width: 100%;
        outline: none;
    }
    .xr-search-box input::placeholder {
        color: var(--text-secondary, #94A3B8);
    }
    
    /* Result Cards */
    .part-card {
        background-color: var(--card-bg, #FFFFFF);
        border: 1px solid var(--border-color, #E2E8F0);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        margin-bottom: 1rem;
        animation: fadeIn 0.2s ease-out;
    }
    .part-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 1px solid var(--border-color, #E2E8F0);
        padding-bottom: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .part-title-group {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }
    .part-title-text {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary, #0F172A);
    }
    .part-model-info {
        font-size: 0.75rem;
        color: var(--text-secondary, #64748B);
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.15rem;
    }
    .part-model-tag {
        background-color: var(--bg-hover, #F1F5F9);
        color: var(--text-secondary, #475569);
        padding: 0.1rem 0.35rem;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.7rem;
    }
    .part-stock-pill {
        background-color: #ECFDF5;
        border: 1px solid #A7F3D0;
        color: #059669;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .part-stock-pill.empty {
        background-color: #FEF2F2;
        border: 1px solid #FCA5A5;
        color: #DC2626;
    }
    .part-summary-text {
        font-size: 0.78rem;
        color: var(--text-secondary, #64748B);
        margin-bottom: 0.5rem;
    }
    .part-summary-text strong {
        color: var(--text-primary, #0F172A);
    }
    
    /* Table styling for customer table list */
    .part-table-wrapper {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid var(--border-color, #E2E8F0);
        border-radius: 6px;
    }
    .part-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .part-table th {
        background-color: var(--bg-hover, #F8FAFC);
        color: var(--text-secondary, #64748B);
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.5rem 0.75rem;
        position: sticky;
        top: 0;
        border-bottom: 1px solid var(--border-color, #E2E8F0);
        z-index: 10;
    }
    .part-table td {
        padding: 0.5rem 0.75rem;
        font-size: 0.78rem;
        color: var(--text-primary, #0F172A);
        border-bottom: 1px solid var(--border-color, #E2E8F0);
        vertical-align: middle;
    }
    .part-table tbody tr:last-child td {
        border-bottom: none;
    }
    .part-table tbody tr:hover td {
        background-color: var(--bg-hover, #F8FAFC);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    let searchTimeout = null;
    let lastSearchResult = null;
    let stockFilter = 'all'; // 'all' or 'instock'

    const renderResults = () => {
        if (!Array.isArray(lastSearchResult) || lastSearchResult.length === 0) {
            $('#resultContainer').html(`
                <div class="xr-subtitle" style="text-align: center; padding: 4rem 1rem; color: var(--text-secondary);">
                    No parts or customers match that keyword.
                </div>
            `);
            return;
        }

        // Group by partInventoryCd in JavaScript
        const grouped = {};
        lastSearchResult.forEach(item => {
            const cd = item.partInventoryCd;
            const qty = parseInt(item.qtyOnHand || 0);

            // Filter by stock if toggled
            if (stockFilter === 'instock' && qty <= 0) {
                return;
            }

            if (!grouped[cd]) {
                grouped[cd] = {
                    partInventoryCd: cd,
                    partDesc: item.partDesc || '-',
                    qtyOnHand: qty,
                    frames: new Set(),
                    customers: []
                };
            }
            if (item.frame) {
                grouped[cd].frames.add(item.frame.trim());
            }
            grouped[cd].customers.push(item);
        });

        const groupsArray = Object.values(grouped);

        if (groupsArray.length === 0) {
            $('#resultContainer').html(`
                <div style="text-align: center; padding: 4rem 1rem; color: var(--text-secondary);">
                    There are no parts in stock that match those keywords.
                </div>
            `);
            return;
        }

        // Generate HTML for each part group
        let html = '';
        groupsArray.forEach(group => {
            const uniqueFrames = Array.from(group.frames);
            const totalUnits = group.customers.length;
            
            // Count unique customer names
            const uniqueCusts = new Set(group.customers.map(c => c.CustomerName));
            const totalCusts = uniqueCusts.size;

            // Collect all individual models from comma-separated frames
            let allModels = [];
            uniqueFrames.forEach(f => {
                if (f) {
                    const splitModels = f.split(',').map(m => m.trim()).filter(Boolean);
                    allModels.push(...splitModels);
                }
            });
            allModels = Array.from(new Set(allModels)); // Deduplicate

            const limit = 8;
            const displayedModels = allModels.slice(0, limit);
            const hasMore = allModels.length > limit;
            const moreCount = allModels.length - limit;

            let modelsHtml = '';
            displayedModels.forEach(m => {
                modelsHtml += `<span class="part-model-tag">${m}</span> `;
            });
            if (hasMore) {
                const encodedModels = encodeURIComponent(JSON.stringify(allModels));
                modelsHtml += `<span class="part-model-tag more-trigger" data-label="Part Number" data-part="${group.partInventoryCd}" data-models="${encodedModels}" style="cursor: pointer; background-color: var(--accent-blue, #3B82F6); color: #FFFFFF; font-weight: 600;" title="Klik untuk melihat semua model">+${moreCount}</span>`;
            }

            // Header part info
            const stockClass = group.qtyOnHand > 0 ? 'part-stock-pill' : 'part-stock-pill empty';
            const stockText = group.qtyOnHand > 0 ? `Stok: ${group.qtyOnHand.toLocaleString('id-ID')}` : 'Stok: Kosong';

            let tableRows = '';
            group.customers.forEach(cust => {
                let rowModels = [];
                if (cust.frame) {
                    rowModels = cust.frame.split(',').map(m => m.trim()).filter(Boolean);
                }
                rowModels = Array.from(new Set(rowModels)); // Deduplicate

                const rowLimit = 5;
                const displayedRowModels = rowModels.slice(0, rowLimit);
                const rowHasMore = rowModels.length > rowLimit;
                const rowMoreCount = rowModels.length - rowLimit;

                let rowModelsHtml = '';
                displayedRowModels.forEach(m => {
                    rowModelsHtml += `<span class="part-model-tag">${m}</span> `;
                });
                if (rowHasMore) {
                    const encodedRowModels = encodeURIComponent(JSON.stringify(rowModels));
                    rowModelsHtml += `<span class="part-model-tag more-trigger" data-label="Serial Number" data-part="${cust.SerialNumber || '-'}" data-models="${encodedRowModels}" style="cursor: pointer; background-color: var(--accent-blue, #3B82F6); color: #FFFFFF; font-weight: 600;" title="Klik untuk melihat semua model">+${rowMoreCount}</span>`;
                }

                tableRows += `
                    <tr>
                        <td style="font-weight: 600;">${cust.CustomerName || '-'}</td>
                        <td style="font-variant-numeric: tabular-nums;">${cust.SerialNumber || '-'}</td>
                        <td>${rowModelsHtml || '-'}</td>
                    </tr>
                `;
            });

            html += `
                <div class="part-card">
                    <div class="part-card-header">
                        <div class="part-title-group">
                            <div class="part-title-text">${group.partInventoryCd} — ${group.partDesc}</div>
                            <div class="part-model-info">
                                Used in the following models: ${modelsHtml}
                            </div>
                        </div>
                        <div class="${stockClass}">${stockText}</div>
                    </div>
                    
                    <div class="part-summary-text">
                        <strong>${totalUnits} unit</strong> terpasang di <strong>${totalCusts} customer</strong> berpotensi butuh part ini:
                    </div>
                    
                    <div class="part-table-wrapper">
                        <table class="part-table">
                            <thead>
                                <tr>
                                    <th>CUSTOMER</th>
                                    <th>SERIAL</th>
                                    <th>MODEL</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        });

        $('#resultContainer').html(html);
    };

    const performSearch = (part) => {
        $('#resultContainer').html(`
            <div style="text-align: center; padding: 4rem 1rem; color: var(--text-secondary);">
                <i class="fa-solid fa-circle-notch fa-spin" style="color: var(--accent-blue); font-size: 2rem; margin-bottom: 0.75rem;"></i>
                <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary);">Searching for a cross-reference...</div>
            </div>
        `);

        $.ajax({
            url: '<?php echo $data["get_customers_by_part_url"]; ?>',
            type: 'GET',
            data: { part: part },
            dataType: 'json',
            success: function(res) {
                lastSearchResult = res;
                renderResults();
            },
            error: function() {
                $('#resultContainer').html(`
                    <div style="text-align: center; padding: 4rem 1rem; color: #EF4444;">
                        The search failed. Please try again.
                    </div>
                `);
            }
        });
    };

    $(document).ready(function() {
        $('#partSearchInput').on('input', function() {
            const val = $(this).val().trim();
            clearTimeout(searchTimeout);
            
            if (val.length < 2) {
                lastSearchResult = null;
                $('#resultContainer').html(`
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 1rem; gap: 1.25rem; animation: fadeIn 0.3s ease-in-out;">
                        <img src="<?= base_url() ?>assets/images/undraw_file-searching_yska.svg" alt="Mulai mencari" style="width: 220px; height: auto; max-width: 100%; opacity: 0.85;">
                        <div class="xr-subtitle" style="text-align: center; color: var(--text-secondary); font-size: 0.85rem; font-weight: 500; line-height: 1.5;">
                            Type at least 2 characters to start your search.
                        </div>
                    </div>
                `);
                return;
            }
            
            searchTimeout = setTimeout(function() {
                performSearch(val);
            }, 300);
        });

        // Toggle filter buttons click handler
        $('.btn-toggle').on('click', function() {
            $('.btn-toggle').removeClass('active');
            $(this).addClass('active');
            stockFilter = $(this).attr('data-filter');
            
            const val = $('#partSearchInput').val().trim();
            if (val.length >= 2 && lastSearchResult !== null) {
                renderResults();
            }
        });

        // Make example labels clickable
        $('.example-search').on('click', function() {
            const val = $(this).text();
            $('#partSearchInput').val(val).trigger('input');
        });

        // Trigger models list modal popup
        $(document).on('click', '.more-trigger', function() {
            const label = $(this).attr('data-label') || 'Part/Unit';
            const part = $(this).attr('data-part');
            const encodedModels = $(this).attr('data-models');
            const models = JSON.parse(decodeURIComponent(encodedModels));

            $('#modalPartLabel').text(label + ': ');
            $('#modalPartNumber').text(part);
            
            let tagsHtml = '';
            models.forEach(m => {
                tagsHtml += `<span class="part-model-tag" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; background-color: var(--bg-hover, #F1F5F9); color: var(--text-secondary, #475569); border-radius: 4px; font-weight: 600;">${m}</span>`;
            });
            
            $('#modalModelsContainer').html(tagsHtml);
            $('#modelsModal').modal('show');
        });
    });
</script>
