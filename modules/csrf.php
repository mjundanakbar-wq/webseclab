<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'CSRF';
$active = 'csrf';

$msg = '';
$msgType = 'info';

$submitted = ($level === 'impossible') ? isset($_POST['Change']) : isset($_GET['Change']);

if ($submitted) {
    switch ($level) {
        case 'low':
            // [LEVEL:low]
            $new  = $_GET['password_new'] ?? '';
            $conf = $_GET['password_conf'] ?? '';

            // Tidak ada token, tidak ada password lama, dan memakai metode GET.
            if ($new !== '' && $new === $conf) {
                $hash = md5($new);
                mysqli_query($conn, "UPDATE users SET password = '$hash' WHERE user_id = " . (int) $_SESSION['user_id']);
                $msg = 'Password berhasil diubah.';
                $msgType = 'success';
            } else {
                $msg = 'Password baru tidak cocok.';
                $msgType = 'error';
            }
            // [END:low]
            break;

        case 'medium':
            // [LEVEL:medium]
            $referer = $_SERVER['HTTP_REFERER'] ?? '';

            // Hanya memeriksa apakah nama server muncul di header Referer (mudah dipalsukan).
            if (stripos($referer, $_SERVER['SERVER_NAME']) !== false) {
                $new  = $_GET['password_new'] ?? '';
                $conf = $_GET['password_conf'] ?? '';
                if ($new !== '' && $new === $conf) {
                    $hash = md5($new);
                    mysqli_query($conn, "UPDATE users SET password = '$hash' WHERE user_id = " . (int) $_SESSION['user_id']);
                    $msg = 'Password berhasil diubah.';
                    $msgType = 'success';
                } else {
                    $msg = 'Password baru tidak cocok.';
                    $msgType = 'error';
                }
            } else {
                $msg = 'Request ditolak: asal permintaan tidak dikenali.';
                $msgType = 'error';
            }
            // [END:medium]
            break;

        case 'high':
            // [LEVEL:high]
            // Token anti-CSRF diperiksa, tetapi masih bisa dicuri bila ada celah XSS.
            if (check_token($_GET['user_token'] ?? '')) {
                $new  = $_GET['password_new'] ?? '';
                $conf = $_GET['password_conf'] ?? '';
                if ($new !== '' && $new === $conf) {
                    $hash = md5($new);
                    mysqli_query($conn, "UPDATE users SET password = '$hash' WHERE user_id = " . (int) $_SESSION['user_id']);
                    $msg = 'Password berhasil diubah.';
                    $msgType = 'success';
                } else {
                    $msg = 'Password baru tidak cocok.';
                    $msgType = 'error';
                }
            } else {
                $msg = 'Token CSRF tidak valid.';
                $msgType = 'error';
            }
            // [END:high]
            break;

        case 'impossible':
            // [LEVEL:impossible]
            $cur  = $_POST['password_current'] ?? '';
            $new  = $_POST['password_new'] ?? '';
            $conf = $_POST['password_conf'] ?? '';
            $row  = find_user($conn, $_SESSION['username']);

            if (!check_token($_POST['user_token'] ?? '')) {
                $msg = 'Token CSRF tidak valid.';
                $msgType = 'error';
            } elseif (!$row || !verify_password($cur, $row['password'])) {
                $msg = 'Password saat ini salah.';
                $msgType = 'error';
            } elseif ($new === '' || $new !== $conf) {
                $msg = 'Password baru tidak cocok.';
                $msgType = 'error';
            } elseif (strlen($new) < 8) {
                $msg = 'Password baru minimal 8 karakter.';
                $msgType = 'error';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE user_id = ?');
                $uid = (int) $_SESSION['user_id'];
                mysqli_stmt_bind_param($stmt, 'si', $hash, $uid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $msg = 'Password berhasil diubah.';
                $msgType = 'success';
            }
            // [END:impossible]
            break;
    }
}

$token = generate_token();

include __DIR__ . '/../includes/header.php';

module_intro(
    'Cross-Site Request Forgery (CSRF)',
    'A01 – Broken Access Control',
    'Simulasi fitur ubah password akun yang sedang login. Pada level rendah, perubahan dapat dipicu dari situs lain tanpa sepengetahuan pengguna.',
    [
        'Perhatikan metode HTTP dan parameter yang dikirim saat password diubah.',
        'Pikirkan bagaimana halaman lain (di luar WebSecLab) dapat memicu permintaan yang sama dari browser korban.',
        'Level Impossible meminta password lama dan token anti-CSRF.',
    ],
    'csrf'
);

ui_alert('warning', 'Modul ini mengubah password akun "' . $_SESSION['username'] . '". Password default bisa dikembalikan lewat database/webseclab.sql.');
if ($msg !== '') {
    ui_alert($msgType, $msg);
}
?>

<form method="<?= $level === 'impossible' ? 'post' : 'get' ?>" class="mt-4 max-w-md rounded-xl border border-slate-200 bg-white p-6">
    <input type="hidden" name="user_token" value="<?= e($token) ?>">

    <?php if ($level === 'impossible'): ?>
        <label class="mb-1 block text-sm font-medium text-slate-700" for="password_current">Password saat ini</label>
        <input id="password_current" name="password_current" type="password" autocomplete="off"
               class="mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    <?php endif; ?>

    <label class="mb-1 block text-sm font-medium text-slate-700" for="password_new">Password baru</label>
    <input id="password_new" name="password_new" type="password" autocomplete="off"
           class="mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">

    <label class="mb-1 block text-sm font-medium text-slate-700" for="password_conf">Konfirmasi password baru</label>
    <input id="password_conf" name="password_conf" type="password" autocomplete="off"
           class="mb-5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">

    <button type="submit" name="Change" value="Change" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Ubah Password</button>
</form>

<?php
view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
