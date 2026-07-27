<div class="xr-container">
    <div style="display: flex; flex-direction: column; gap: 4px; margin-bottom: 0.5rem;">
        <div class="xr-title">Cross-Reference Part → Model → Customer</div>
        <div class="xr-subtitle">Cari 1 part number, temukan seluruh customer yang berpotensi membutuh part tersebut</div>
    </div>
    <div class="xr-search-card" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div class="xr-search-box" style="flex: 1; max-width: 420px;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="partSearchInput" placeholder="Ketik part number atau kata kunci deskripsi..." autofocus>
        </div>
        <div class="stock-toggle-container">
            <button class="btn-toggle active" data-filter="all">Semua</button>
            <button class="btn-toggle" data-filter="instock">Ada Stok</button>
        </div>
    </div>
    <div id="resultContainer">
        <div class="xr-subtitle" style="text-align: center; padding: 4rem 1rem; color: var(--text-secondary);">
            Ketik minimal 2 karakter untuk mulai mencari.
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
                    Tidak ada part atau customer yang cocok dengan kata kunci tersebut.
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
                    Tidak ada part berstok yang cocok dengan kata kunci tersebut.
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

            // Header part info
            const stockClass = group.qtyOnHand > 0 ? 'part-stock-pill' : 'part-stock-pill empty';
            const stockText = group.qtyOnHand > 0 ? `Stok: ${group.qtyOnHand.toLocaleString('id-ID')}` : 'Stok: Kosong';

            let tableRows = '';
            group.customers.forEach(cust => {
                tableRows += `
                    <tr>
                        <td style="font-weight: 600;">${cust.CustomerName || '-'}</td>
                        <td style="font-variant-numeric: tabular-nums;">${cust.SerialNumber || '-'}</td>
                        <td><span class="part-model-tag">${cust.frame || '-'}</span></td>
                    </tr>
                `;
            });

            html += `
                <div class="part-card">
                    <div class="part-card-header">
                        <div class="part-title-group">
                            <div class="part-title-text">${group.partInventoryCd} — ${group.partDesc}</div>
                            <div class="part-model-info">
                                Dipakai di model: ${uniqueFrames.map(f => `<span class="part-model-tag">${f}</span>`).join(' ')}
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
                <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary);">Mencari cross reference...</div>
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
                        Gagal melakukan pencarian. Silakan coba lagi.
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
                    <div class="xr-subtitle" style="text-align: center; padding: 4rem 1rem; color: var(--text-secondary);">
                        Ketik minimal 2 karakter untuk mulai mencari.
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
    });
</script>
