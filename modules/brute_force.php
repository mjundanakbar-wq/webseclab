<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'Brute Force';
$active = 'brute_force';

$attempted = false;
$ok = false;
$row = null;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Login'])) {
    $attempted = true;

    switch ($level) {
        case 'low':
            // [LEVEL:low]
            $user = $_POST['username'] ?? '';
            $pass = $_POST['password'] ?? '';

            // Tidak ada batas percobaan, delay, maupun CAPTCHA.
            $row = find_user($conn, $user);
            $ok  = $row && verify_password($pass, $row['password']);
            // [END:low]
            break;

        case 'medium':
            // [LEVEL:medium]
            $user = $_POST['username'] ?? '';
            $pass = $_POST['password'] ?? '';

            $row = find_user($conn, $user);
            $ok  = $row && verify_password($pass, $row['password']);

            // Hanya delay tetap 2 detik saat gagal: serangan jadi lambat, bukan terhenti.
            if (!$ok) {
                sleep(2);
            }
            // [END:medium]
            break;

        case 'high':
            // [LEVEL:high]
            // Token anti-CSRF ada, tetapi token tampil di form sehingga bisa dibaca skrip penyerang.
            if (!check_token($_POST['user_token'] ?? '')) {
                $ok = false;
                $msg = 'Token CSRF tidak valid.';
                break;
            }
            $user = $_POST['username'] ?? '';
            $pass = $_POST['password'] ?? '';

            $row = find_user($conn, $user);
            $ok  = $row && verify_password($pass, $row['password']);

            // Delay acak 0-3 detik saat gagal.
            if (!$ok) {
                sleep(random_int(0, 3));
            }
            // [END:high]
            break;

        case 'impossible':
            // [LEVEL:impossible]
            if (!check_token($_POST['user_token'] ?? '')) {
                $ok = false;
                $msg = 'Token CSRF tidak valid.';
                break;
            }
            $user = substr(trim($_POST['username'] ?? ''), 0, 50);
            $pass = $_POST['password'] ?? '';

            // Cek status penguncian akun (3 gagal => kunci 15 menit)
            $stmt = mysqli_prepare($conn, 'SELECT attempts, TIMESTAMPDIFF(SECOND, last_attempt, NOW()) AS age FROM login_attempts WHERE username = ?');
            mysqli_stmt_bind_param($stmt, 's', $user);
            mysqli_stmt_execute($stmt);
            $att = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if ($att && (int) $att['attempts'] >= 3 && (int) $att['age'] < 900) {
                $ok = false;
                $msg = 'Terlalu banyak percobaan gagal. Akun dikunci sementara selama 15 menit.';
                break;
            }

            $row = find_user($conn, $user);
            $ok  = $row && verify_password($pass, $row['password']);

            if ($ok) {
                $stmt = mysqli_prepare($conn, 'DELETE FROM login_attempts WHERE username = ?');
                mysqli_stmt_bind_param($stmt, 's', $user);
            } else {
                usleep(random_int(300000, 1000000));
                $n = ($att && (int) $att['age'] < 900) ? (int) $att['attempts'] + 1 : 1;
                $stmt = mysqli_prepare($conn, 'INSERT INTO login_attempts (username, attempts, last_attempt) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE attempts = VALUES(attempts), last_attempt = NOW()');
                mysqli_stmt_bind_param($stmt, 'si', $user, $n);
            }
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // [END:impossible]
            break;
    }

    if (!$ok && $msg === '') {
        $msg = 'Username atau password salah.';
    }
}

$token = generate_token();

include __DIR__ . '/../includes/header.php';

module_intro(
    'Brute Force',
    'A07 – Identification & Authentication Failures',
    'Simulasi form login yang rentan terhadap serangan kamus (dictionary attack). Form ini hanya menguji kredensial, tidak mengubah sesi Anda.',
    [
        'Coba daftar username dan password umum terhadap form di bawah.',
        'Bandingkan waktu respon dan perilaku server pada tiap level keamanan.',
        'Pada level Impossible, perhatikan mekanisme penguncian akun.',
    ],
    'brute_force'
);
?>

<form method="post" class="max-w-md rounded-xl border border-slate-200 bg-white p-6">
    <input type="hidden" name="user_token" value="<?= e($token) ?>">

    <label class="mb-1 block text-sm font-medium text-slate-700" for="username">Username</label>
    <input id="username" name="username" type="text" autocomplete="off"
           class="mb-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">

    <label class="mb-1 block text-sm font-medium text-slate-700" for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="off"
           class="mb-5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">

    <button type="submit" name="Login" value="Login" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Login</button>
</form>

<?php
if ($attempted) {
    if ($ok) {
        ui_alert('success', 'Login berhasil! Selamat datang, ' . $row['username'] . '.');
    } else {
        ui_alert('error', $msg);
    }
}

view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
