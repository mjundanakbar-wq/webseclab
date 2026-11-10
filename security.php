<?php
require_once __DIR__ . '/config/database.php';
require_login();

$descriptions = [
    'low'        => 'Tanpa filter, validasi, atau sanitasi sama sekali.',
    'medium'     => 'Filter / blacklist dasar yang mudah dilewati (bypass).',
    'high'       => 'Filter lebih ketat, namun masih memiliki celah teknis.',
    'impossible' => 'Secure coding: prepared statement, validasi ketat, token anti-CSRF, escape output.',
];

$saved = false;
$reset = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ResetLab']) && check_token($_POST['user_token'] ?? '')) {
        reset_lab_data($conn, __DIR__ . '/uploads');
        $reset = true;
    } else {
        $new = $_POST['security_level'] ?? '';
        if (in_array($new, SECURITY_LEVELS, true)) {
            $_SESSION['security_level'] = $new;
            $saved = true;
        }
    }
}

$token = generate_token();

$page_title = 'Security Level';
$active = 'security';
include __DIR__ . '/includes/header.php';
?>

<h1 class="text-2xl font-bold text-slate-900">Security Level</h1>
<p class="mt-2 text-sm text-slate-600">Level yang dipilih berlaku untuk seluruh modul (disimpan di <code class="rounded bg-slate-100 px-1">$_SESSION['security_level']</code>).</p>

<?php if ($saved) { ui_alert('success', 'Security level diubah menjadi: ' . strtoupper(security_level())); } ?>
<?php if ($reset) { ui_alert('success', 'Data lab berhasil direset: password akun, akun terkunci, buku tamu, dan file upload sudah kembali ke kondisi awal.'); } ?>

<form method="post" class="mt-6 space-y-3">
    <?php foreach ($descriptions as $key => $desc): ?>
        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:ring-2 has-[:checked]:ring-indigo-100">
            <input type="radio" name="security_level" value="<?= e($key) ?>" class="mt-1" <?= security_level() === $key ? 'checked' : '' ?>>
            <span>
                <span class="block text-sm font-semibold uppercase text-slate-900"><?= e($key) ?></span>
                <span class="block text-sm text-slate-600"><?= e($desc) ?></span>
            </span>
        </label>
    <?php endforeach; ?>

    <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
</form>

<div class="mt-10 rounded-xl border border-amber-200 bg-amber-50 p-5">
    <h2 class="text-sm font-semibold text-amber-800">Reset Data Lab</h2>
    <p class="mt-1 text-sm text-amber-800">
        Mengembalikan password semua akun, membuka akun yang terkunci (modul Brute Force), mengosongkan buku tamu
        (modul XSS Stored), dan menghapus file hasil upload (modul File Upload) ke kondisi awal.
        Pakai ini sebelum presentasi/demo agar hasil percobaan sebelumnya tidak ikut terlihat.
    </p>
    <form method="post" class="mt-3" onsubmit="return confirm('Reset semua data percobaan ke kondisi awal?');">
        <input type="hidden" name="user_token" value="<?= e($token) ?>">
        <button type="submit" name="ResetLab" value="1" class="rounded-lg border border-amber-300 bg-white px-5 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">
            Reset Data Lab
        </button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
