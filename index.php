<?php
require_once __DIR__ . '/config/database.php';
require_login();

$cards = [
    ['brute_force',       'Brute Force',       'A07 – Identification & Authentication Failures', 'Serangan kamus pada form login tanpa rate-limiting.'],
    ['command_injection', 'Command Injection', 'A03 – Injection',                                'Fitur ping yang meneruskan input ke perintah sistem operasi.'],
    ['csrf',              'CSRF',              'A01 – Broken Access Control',                    'Ubah password tanpa konfirmasi dan token anti-CSRF.'],
    ['file_upload',       'File Upload',       'A04 – Insecure Design',                          'Upload foto profil tanpa pembatasan tipe berkas.'],
    ['lfi',               'File Inclusion',    'A01 – Broken Access Control',                    'Parameter page di-include langsung tanpa validasi.'],
    ['sql_injection',     'SQL Injection',     'A03 – Injection',                                'Pencarian user dengan query yang dirangkai dari input.'],
    ['xss_reflected',     'XSS (Reflected)',   'A03 – Injection',                                'Input nama dicetak kembali ke HTML tanpa escape.'],
    ['xss_stored',        'XSS (Stored)',      'A03 – Injection',                                'Buku tamu yang menyimpan payload ke database.'],
];

$page_title = 'Dashboard';
$active = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

<h1 class="text-2xl font-bold text-slate-900">Selamat datang, <?= e($_SESSION['username']) ?></h1>
<p class="mt-2 max-w-2xl text-sm text-slate-600">
    WebSecLab adalah laboratorium keamanan web interaktif untuk belajar simulasi serangan dan secure coding.
    Pilih modul, ubah <a class="font-medium text-indigo-600 hover:underline" href="<?= e(BASE_URL) ?>/security.php">security level</a>,
    lalu gunakan tombol <em>View Source</em> untuk membandingkan kode rentan dan kode aman.
</p>

<div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
    <strong>Peringatan:</strong> aplikasi ini sengaja rentan. Jangan diunggah ke server publik dan jangan dijalankan di jaringan yang tidak terpercaya.
</div>

<div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($cards as $i => [$key, $name, $owasp, $desc]): ?>
        <a href="<?= e(BASE_URL) ?>/modules/<?= e($key) ?>.php"
           class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-sm font-bold text-indigo-700"><?= $i + 1 ?></span>
                <span class="text-xs text-slate-400 group-hover:text-indigo-500">Buka &rarr;</span>
            </div>
            <h2 class="mt-3 font-semibold text-slate-900"><?= e($name) ?></h2>
            <p class="mt-0.5 text-xs font-medium text-indigo-600"><?= e($owasp) ?></p>
            <p class="mt-2 text-sm text-slate-600"><?= e($desc) ?></p>
        </a>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
