<div class="page-header">
    <h1><?= isset($page_title) ? $page_title : 'Fitur' ?></h1>
    <p><?= isset($page_subtitle) ? $page_subtitle : 'Halaman ini sedang dalam tahap pengembangan' ?></p>
</div>

<div class="table-card" style="text-align: center; padding: 4rem 2rem; margin-top: 1rem;">
    <div style="width: 80px; height: 80px; background: rgba(37, 99, 235, 0.08); color: var(--accent-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 2.2rem; border: 1px solid rgba(37, 99, 235, 0.2);">
        <i class="fa-solid fa-screwdriver-wrench"></i>
    </div>
    
    <span style="background: #EFF6FF; color: var(--accent-blue); padding: 0.25rem 0.85rem; border-radius: 20px; font-size: 0.78rem; font-weight: 700; border: 1px solid #BFDBFE; display: inline-block; margin-bottom: 1rem;">
        UNDER MAINTENANCE / IN DEVELOPMENT
    </span>
    
    <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">
        Fitur <?= isset($page_title) ? $page_title : '' ?> Sedang Disiapkan
    </h2>
    
    <p style="color: var(--text-secondary); font-size: 0.88rem; max-width: 480px; margin: 0 auto 2rem auto; line-height: 1.6;">
        Halaman ini sedang dalam tahap perancangan dan integrasi data. Silakan kembali lagi nanti atau akses menu <strong>Katalog Parts</strong> yang telah aktif.
    </p>

    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
        <a href="<?= site_url('spareparts/katalog_part_list') ?>" class="btn-export" style="text-decoration: none !important;">
            <i class="fa-solid fa-cube"></i> Buka Katalog Parts
        </a>
        <a href="<?= site_url('dashboard') ?>" class="btn-reset" style="text-decoration: none !important;">
            <i class="fa-solid fa-house"></i> Ke Dashboard
        </a>
    </div>
</div>
