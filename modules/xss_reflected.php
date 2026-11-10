<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'XSS (Reflected)';
$active = 'xss_reflected';

$html = null;

if (isset($_GET['name']) && $_GET['name'] !== '') {
    switch ($level) {
        case 'low':
            // [LEVEL:low]
            // Input dicetak kembali ke HTML apa adanya.
            $html = 'Halo, ' . $_GET['name'];
            // [END:low]
            break;

        case 'medium':
            // [LEVEL:medium]
            // Hanya menghapus tag <script> huruf kecil persis (case-sensitive).
            $name = str_replace('<script>', '', $_GET['name']);
            $html = 'Halo, ' . $name;
            // [END:medium]
            break;

        case 'high':
            // [LEVEL:high]
            // Regex untuk memblokir kata "script", tetapi tag HTML lain masih lolos.
            $name = preg_replace('/<(.*)s(.*)c(.*)r(.*)i(.*)p(.*)t/i', '', $_GET['name']);
            $html = 'Halo, ' . $name;
            // [END:high]
            break;

        case 'impossible':
            // [LEVEL:impossible]
            if (!check_token($_GET['user_token'] ?? '')) {
                $html = 'Token CSRF tidak valid.';
                break;
            }
            // Escape output sesuai konteks HTML.
            $name = htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8');
            $html = 'Halo, ' . $name;
            // [END:impossible]
            break;
    }
}

$token = generate_token();

include __DIR__ . '/../includes/header.php';

module_intro(
    'Cross-Site Scripting (XSS) – Reflected',
    'A03 – Injection',
    'Form nama yang langsung menampilkan pesan sambutan. Input yang dicetak kembali tanpa escape dapat mengeksekusi JavaScript di browser korban.',
    [
        'Masukkan nama biasa dahulu, lalu coba sisipkan tag HTML dan perhatikan hasilnya di halaman.',
        'Contoh uji klasik: <script>alert(document.cookie)</script>',
        'Pada level Medium dan High, pikirkan cara lain menjalankan JavaScript tanpa tag yang difilter.',
    ],
    'xss_reflected'
);
?>

<form method="get" class="max-w-md rounded-xl border border-slate-200 bg-white p-6">
    <input type="hidden" name="user_token" value="<?= e($token) ?>">
    <label class="mb-1 block text-sm font-medium text-slate-700" for="name">Siapa nama Anda?</label>
    <input id="name" name="name" type="text" autocomplete="off"
           class="mb-5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Kirim</button>
</form>

<?php
if ($html !== null) {
    // Sengaja dicetak tanpa escape tambahan di sini; perlakuan input ditentukan oleh blok level di atas.
    echo '<div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-sm"><pre class="whitespace-pre-wrap font-sans">' . $html . '</pre></div>';
}

view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
