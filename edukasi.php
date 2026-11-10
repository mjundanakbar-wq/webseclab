<?php
require_once __DIR__ . '/config/database.php';
require_login();

$labels = [
    'brute_force'       => ['1. Brute Force', 'A07 – Identification & Authentication Failures'],
    'command_injection' => ['2. Command Injection', 'A03 – Injection'],
    'csrf'              => ['3. CSRF', 'A01 – Broken Access Control'],
    'file_upload'       => ['4. File Upload', 'A04 – Insecure Design'],
    'lfi'               => ['5. File Inclusion (LFI)', 'A01 – Broken Access Control'],
    'sql_injection'     => ['6. SQL Injection', 'A03 – Injection'],
    'xss_reflected'     => ['7. XSS (Reflected)', 'A03 – Injection'],
    'xss_stored'        => ['8. XSS (Stored)', 'A03 – Injection'],
];

$m = $_GET['m'] ?? '';
if (!isset($labels[$m])) {
    http_response_code(404);
    $page_title = 'Edukasi';
    $active = '';
    include __DIR__ . '/includes/header.php';
    ui_alert('error', 'Modul edukasi tidak ditemukan.');
    echo '<p class="mt-4"><a class="text-indigo-600 hover:underline" href="' . e(BASE_URL) . '/index.php">&larr; Kembali ke Dashboard</a></p>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$content = require __DIR__ . '/config/education_content.php';
$data = $content[$m];
[$title, $owasp] = $labels[$m];

$page_title = 'Edukasi: ' . $title;
$active = 'edukasi_' . $m;
include __DIR__ . '/includes/header.php';
?>

<div class="mb-6">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-2xl">📚</span>
        <h1 class="text-2xl font-bold text-slate-900">Edukasi: <?= e($title) ?></h1>
        <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700"><?= e($owasp) ?></span>
    </div>
    <p class="mt-2 text-sm text-slate-600">
        Halaman ini menjelaskan konsep dan mitigasi kerentanan ini secara teori, terpisah dari praktik eksploitasi.
    </p>
</div>

<div class="rounded-xl border border-slate-200 bg-white p-6">
    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Konsep & Cara Kerja</h2>
    <div class="mt-3 space-y-3 text-sm leading-relaxed text-slate-700">
        <?php foreach ($data['concept'] as $para): ?>
            <p><?= e($para) ?></p>
        <?php endforeach; ?>
    </div>
</div>

<div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-6">
    <h2 class="text-sm font-semibold uppercase tracking-wider text-emerald-700">Cara Mitigasi / Pencegahan</h2>
    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-emerald-900">
        <?php foreach ($data['mitigation'] as $tip): ?>
            <li><?= e($tip) ?></li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="mt-4 rounded-xl border border-slate-200 bg-white p-6">
    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Video Penjelasan</h2>
<?php if (!empty($data['youtube_id'])): ?>
            <?php if (is_array($data['youtube_id'])): ?>
                <?php foreach ($data['youtube_id'] as $yt_id): ?>
                    <?php if (!empty($yt_id)): ?>
                        <div class="mt-3 aspect-video w-full overflow-hidden rounded-lg">
                            <iframe class="h-full w-full"
                                    src="https://www.youtube-nocookie.com/embed/<?= e($yt_id) ?>"
                                    title="Video edukasi: <?= e($title) ?>"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="mt-3 aspect-video w-full overflow-hidden rounded-lg">
                    <iframe class="h-full w-full"
                            src="https://www.youtube-nocookie.com/embed/<?= e($data['youtube_id']) ?>"
                            title="Video edukasi: <?= e($title) ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="mt-3 text-sm text-slate-500">
                Belum ada video untuk modul ini. Tambahkan ID video YouTube pada
                <code class="rounded bg-slate-100 px-1">config/education_content.php</code>
                (field <code class="rounded bg-slate-100 px-1">youtube_id</code>).
            </p>
        <?php endif; ?>
</div>

<div class="mt-6">
    <a href="<?= e(BASE_URL) ?>/modules/<?= e($m) ?>.php" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
        &larr; Kembali ke Praktik <?= e($title) ?>
    </a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
