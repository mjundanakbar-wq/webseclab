<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'File Inclusion';
$active = 'lfi';

$content = null;
$error = '';

if (isset($_GET['page']) && $_GET['page'] !== '') {
    // Direktori kerja = folder pages/ (relatif terhadap sinilah parameter "page" di-include)
    chdir(__DIR__ . '/../pages');

    switch ($level) {
        case 'low':
            // [LEVEL:low]
            $page = $_GET['page'];

            // Parameter langsung masuk ke include() tanpa validasi.
            ob_start();
            include $page;
            $content = ob_get_clean();
            // [END:low]
            break;

        case 'medium':
            // [LEVEL:medium]
            $page = $_GET['page'];

            // Filter satu kali (tidak rekursif): mudah dilewati.
            $page = str_replace(['http://', 'https://'], '', $page);
            $page = str_replace(['../', '..\\'], '', $page);

            ob_start();
            include $page;
            $content = ob_get_clean();
            // [END:medium]
            break;

        case 'high':
            // [LEVEL:high]
            $page = $_GET['page'];

            // Hanya memeriksa awalan nama ("file*"). Pola ini masih bisa dipenuhi oleh input lain.
            if (!fnmatch('file*', $page)) {
                $error = 'ERROR: File tidak ditemukan!';
                break;
            }

            ob_start();
            include $page;
            $content = ob_get_clean();
            // [END:high]
            break;

        case 'impossible':
            // [LEVEL:impossible]
            $page = $_GET['page'];

            // Whitelist eksplisit: hanya berkas yang dikenal yang boleh di-include.
            $allowed = ['file1.php', 'file2.php', 'file3.php'];
            if (!in_array($page, $allowed, true)) {
                $error = 'ERROR: File tidak ditemukan!';
                break;
            }

            ob_start();
            include $page;
            $content = ob_get_clean();
            // [END:impossible]
            break;
    }
}

include __DIR__ . '/../includes/header.php';

module_intro(
    'Local File Inclusion (LFI)',
    'A01 – Broken Access Control',
    'Simulasi page viewer dinamis berbasis parameter URL (?page=file1.php). Parameter yang tidak divalidasi dapat dipakai untuk membaca berkas lain di server.',
    [
        'Klik salah satu halaman di bawah dan perhatikan parameter page pada URL.',
        'Coba ubah parameter agar menunjuk ke berkas di luar folder pages/ (mis. ../../../../etc/passwd; di Windows gunakan berkas sistem seperti win.ini).',
        'Bandingkan bagaimana tiap level memfilter parameter.',
    ],
    'lfi'
);
?>

<div class="flex flex-wrap gap-2">
    <?php foreach (['file1.php' => 'Tentang', 'file2.php' => 'Kontak', 'file3.php' => 'Bantuan'] as $f => $label): ?>
        <a href="?page=<?= e($f) ?>" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:border-indigo-300 hover:text-indigo-600">
            <?= e($label) ?> <span class="font-mono text-xs text-slate-400">(<?= e($f) ?>)</span>
        </a>
    <?php endforeach; ?>
</div>

<?php
if ($error !== '') {
    ui_alert('error', $error);
}
if ($content !== null) {
    echo '<div class="mt-4 rounded-xl border border-slate-200 bg-white p-5 text-sm">';
    if (strip_tags($content) === $content) {
        // Teks biasa (mis. isi berkas sistem) ditampilkan apa adanya dalam blok kode.
        echo '<pre class="overflow-x-auto whitespace-pre-wrap font-mono text-xs text-slate-800">' . e($content) . '</pre>';
    } else {
        echo $content;
    }
    echo '</div>';
}

view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
