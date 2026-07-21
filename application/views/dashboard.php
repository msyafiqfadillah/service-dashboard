<div class="header">
    <div class="header-title">
        <h1><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
        <p><?= isset($page_subtitle) ? $page_subtitle : 'Ringkasan data...' ?></p>
    </div>
    <div class="header-actions">
        <button class="btn" style="background-color: transparent;">
            <i class="fa-solid fa-arrows-rotate"></i> Refresh
        </button>
    </div>
</div>

<div class="content-area">
    <p style="opacity: 0.5;">Belum ada data (tampilan dikosongkan)</p>
</div>
