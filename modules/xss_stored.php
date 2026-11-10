<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'XSS (Stored)';
$active = 'xss_stored';

$msg = '';
$msgType = 'info';

// Reset buku tamu (fasilitas lab)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Clear'])) {
    mysqli_query($conn, "DELETE FROM guestbook");
    mysqli_query($conn, "INSERT INTO guestbook (name, comment) VALUES ('Admin', 'Selamat datang di buku tamu WebSecLab!')");
    $msg = 'Buku tamu direset.';
    $msgType = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Sign'])) {
    switch ($level) {
        case 'low':
            // [LEVEL:low]
            $name    = $_POST['name'] ?? '';
            $message = $_POST['message'] ?? '';

            // Disimpan apa adanya, tanpa sanitasi.
            $stmt = mysqli_prepare($conn, 'INSERT INTO guestbook (name, comment) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'ss', $name, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // [END:low]
            break;

        case 'medium':
            // [LEVEL:medium]
            $name    = $_POST['name'] ?? '';
            $message = trim($_POST['message'] ?? '');

            // Pesan dibersihkan dengan baik, tetapi nama hanya dihapus pola <script> persis.
            $message = htmlspecialchars(strip_tags($message), ENT_QUOTES, 'UTF-8');
            $name    = str_replace('<script>', '', $name);

            $stmt = mysqli_prepare($conn, 'INSERT INTO guestbook (name, comment) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'ss', $name, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // [END:medium]
            break;

        case 'high':
            // [LEVEL:high]
            $name    = $_POST['name'] ?? '';
            $message = trim($_POST['message'] ?? '');

            // Pesan aman; nama hanya difilter dengan regex "script" sehingga tag lain masih lolos.
            $message = htmlspecialchars(strip_tags($message), ENT_QUOTES, 'UTF-8');
            $name    = preg_replace('/<(.*)s(.*)c(.*)r(.*)i(.*)p(.*)t/i', '', $name);

            $stmt = mysqli_prepare($conn, 'INSERT INTO guestbook (name, comment) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'ss', $name, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // [END:high]
            break;

        case 'impossible':
            // [LEVEL:impossible]
            if (!check_token($_POST['user_token'] ?? '')) {
                $msg = 'Token CSRF tidak valid.';
                $msgType = 'error';
                break;
            }
            // Data disimpan apa adanya (sudah via prepared statement) dan di-escape saat ditampilkan.
            $name    = trim($_POST['name'] ?? '');
            $message = trim($_POST['message'] ?? '');

            $stmt = mysqli_prepare($conn, 'INSERT INTO guestbook (name, comment) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'ss', $name, $message);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // [END:impossible]
            break;
    }

    if ($msg === '') {
        $msg = 'Pesan berhasil disimpan.';
        $msgType = 'success';
    }
}

$entries = [];
$res = mysqli_query($conn, 'SELECT name, comment, created_at FROM guestbook ORDER BY comment_id DESC');
while ($res && ($r = mysqli_fetch_assoc($res))) {
    $entries[] = $r;
}

$token = generate_token();

include __DIR__ . '/../includes/header.php';

module_intro(
    'Cross-Site Scripting (XSS) – Stored',
    'A03 – Injection',
    'Buku tamu yang menyimpan pesan ke database. Payload yang tersimpan akan dieksekusi setiap kali halaman dibuka oleh pengguna lain.',
    [
        'Kirim pesan biasa, lalu coba sisipkan tag HTML dan muat ulang halaman.',
        'Perhatikan field mana (nama atau pesan) yang disaring pada tiap level, dan batasan panjang di sisi client.',
        'Gunakan tombol Reset untuk membersihkan payload yang tersimpan.',
    ],
    'xss_stored'
);

if ($msg !== '') {
    ui_alert($msgType, $msg);
}
?>

<form method="post" class="mt-4 max-w-md rounded-xl border border-slate-200 bg-white p-6">
    <input type="hidden" name="user_token" value="<?= e($token) ?>">

    <label class="mb-1 block text-sm font-medium text-slate-700" for="name">Nama</label>
    <input id="name" name="name" type="text" maxlength="100" autocomplete="off"
           class="mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">

    <label class="mb-1 block text-sm font-medium text-slate-700" for="message">Pesan</label>
    <textarea id="message" name="message" rows="3" maxlength="300"
              class="mb-5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>

    <div class="flex gap-2">
        <button type="submit" name="Sign" value="Sign" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Kirim</button>
        <button type="submit" name="Clear" value="Clear" formnovalidate class="rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</button>
    </div>
</form>

<div class="mt-6 space-y-3">
    <?php foreach ($entries as $r): ?>
        <?php
        $name    = $r['name'];
        $comment = $r['comment'];
        if ($level === 'impossible') {
            // [LEVEL:impossible]
            // Output di-escape saat ditampilkan, sehingga data lama berisi payload pun tidak dieksekusi.
            $name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
            // [END:impossible]
        }
        ?>
        <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-slate-900"><?= $name ?></span>
                <span class="text-xs text-slate-400"><?= e($r['created_at']) ?></span>
            </div>
            <p class="mt-1 text-slate-600"><?= $comment ?></p>
        </div>
    <?php endforeach; ?>
</div>

<?php
view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
