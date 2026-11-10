<?php
/**
 * WebSecLab - Koneksi database, session, dan fungsi bantu.
 *
 * PERINGATAN: Aplikasi ini SENGAJA dibuat rentan untuk pembelajaran.
 * Jalankan HANYA di localhost / jaringan lab yang terisolasi.
 */

// Guard: file ini bisa ikut ter-include oleh modul LFI. Jangan dieksekusi dua kali.
if (defined('WEBSECLAB_LOADED')) {
    return;
}
define('WEBSECLAB_LOADED', true);

// Nilai default untuk XAMPP (localhost). Saat dijalankan lewat Docker,
// docker-compose.yml mengisi environment variable di bawah ini (lihat DB_HOST dkk),
// sehingga file ini TIDAK perlu diubah manual antara XAMPP dan Docker.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'webseclab');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Matikan exception mysqli supaya pesan error bisa ditampilkan (dibutuhkan untuk demo SQLi).
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    http_response_code(500);
    die('Koneksi database gagal: ' . htmlspecialchars((string) mysqli_connect_error())
        . '. Pastikan MySQL aktif dan database "webseclab" sudah di-import (database/webseclab.sql).');
}
mysqli_set_charset($conn, 'utf8mb4');

// ---------------------------------------------------------------------------
// BASE_URL: path web menuju folder proyek (mis. /webseclab)
// ---------------------------------------------------------------------------
$_docRoot  = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$_projRoot = str_replace('\\', '/', (string) realpath(__DIR__ . '/..'));
if ($_docRoot !== '' && $_projRoot !== '' && stripos($_projRoot, $_docRoot) === 0) {
    define('BASE_URL', rtrim(substr($_projRoot, strlen($_docRoot)), '/'));
} else {
    define('BASE_URL', '/webseclab');
}

// ---------------------------------------------------------------------------
// Security level (Low / Medium / High / Impossible)
// ---------------------------------------------------------------------------
const SECURITY_LEVELS = ['low', 'medium', 'high', 'impossible'];

if (!isset($_SESSION['security_level']) || !in_array($_SESSION['security_level'], SECURITY_LEVELS, true)) {
    $_SESSION['security_level'] = 'low';
}

function security_level(): string
{
    return $_SESSION['security_level'];
}

// ---------------------------------------------------------------------------
// Fungsi bantu umum
// ---------------------------------------------------------------------------
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function generate_token(): string
{
    $_SESSION['user_token'] = bin2hex(random_bytes(16));
    return $_SESSION['user_token'];
}

function check_token($token): bool
{
    return isset($_SESSION['user_token'])
        && is_string($token)
        && hash_equals($_SESSION['user_token'], $token);
}

/** Ambil satu baris user berdasarkan username (prepared statement). */
function find_user($conn, string $username): ?array
{
    $stmt = mysqli_prepare($conn, 'SELECT user_id, username, password, avatar FROM users WHERE username = ?');
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

/**
 * Verifikasi password. Data lab memakai MD5 (agar hasil SQLi mudah dianalisis),
 * sedangkan level Impossible menyimpan password dengan password_hash() (bcrypt).
 */
function verify_password(string $plain, string $stored): bool
{
    if (strpos($stored, '$2y$') === 0 || strpos($stored, '$argon') === 0) {
        return password_verify($plain, $stored);
    }
    return hash_equals(strtolower($stored), md5($plain));
}

// ---------------------------------------------------------------------------
// View Source: ambil blok kode yang BENAR-BENAR dieksekusi pada level aktif
// ---------------------------------------------------------------------------
function dedent_block(string $block): string
{
    $lines = preg_split('/\R/', rtrim($block));
    $min = null;
    foreach ($lines as $l) {
        if (trim($l) === '') {
            continue;
        }
        $indent = strlen($l) - strlen(ltrim($l));
        $min = ($min === null) ? $indent : min($min, $indent);
    }
    $min = $min ?? 0;
    return implode("\n", array_map(function ($l) use ($min) {
        return (string) substr($l, $min);
    }, $lines));
}

function extract_level_source(string $file, string $level): string
{
    $code = @file_get_contents($file);
    if ($code === false) {
        return '// File sumber tidak ditemukan.';
    }
    $lv = preg_quote($level, '/');
    $pattern = '/^[ \t]*\/\/ \[LEVEL:' . $lv . '\][ \t]*\R(.*?)^[ \t]*\/\/ \[END:' . $lv . '\]/ms';
    if (!preg_match_all($pattern, $code, $m) || empty($m[1])) {
        return '// (Tidak ada blok kode untuk level ini)';
    }
    return implode("\n\n// ...\n\n", array_map('dedent_block', $m[1]));
}

function view_source_block(string $file): void
{
    $level = security_level();
    $src = extract_level_source($file, $level);
    $labels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'impossible' => 'Impossible'];
    ?>
    <div class="mt-8">
        <button type="button"
                onclick="document.getElementById('src-box').classList.toggle('hidden')"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400">
            &lt;/&gt; View Source
        </button>
        <div id="src-box" class="hidden mt-3 overflow-hidden rounded-xl border border-slate-700 bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-700 px-4 py-2 text-xs text-slate-300">
                <span>Kode PHP pada level <strong class="text-white"><?= e($labels[$level] ?? $level) ?></strong></span>
                <span class="text-slate-500"><?= e(basename($file)) ?></span>
            </div>
            <pre class="overflow-x-auto p-4 text-xs leading-relaxed text-slate-100"><code><?= e($src) ?></code></pre>
        </div>
    </div>
    <?php
}

// ---------------------------------------------------------------------------
// Komponen UI kecil
// ---------------------------------------------------------------------------
function ui_alert(string $type, string $message): void
{
    $styles = [
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'error'   => 'border-red-200 bg-red-50 text-red-800',
        'info'    => 'border-sky-200 bg-sky-50 text-sky-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
    ];
    $cls = $styles[$type] ?? $styles['info'];
    echo '<div class="mt-4 rounded-lg border px-4 py-3 text-sm ' . $cls . '">' . e($message) . '</div>';
}

/**
 * Mengembalikan seluruh data lab ke kondisi bersih (dipakai tombol "Reset Data Lab"
 * di halaman Security Level). Berguna sebelum demo/presentasi agar hasil percobaan
 * sebelumnya (password berubah, akun terkunci, payload XSS tersimpan, file upload)
 * tidak ikut terbawa.
 */
function reset_lab_data($conn, string $uploadDir): void
{
    // Password default (MD5, disengaja untuk kebutuhan modul SQL Injection).
    $defaults = [
        'admin'   => '5f4dcc3b5aa765d61d8327deb882cf99', // password
        'gordonb' => 'e99a18c428cb38d5f260853678922e03', // abc123
        '1337'    => '8d3533d75ae2c3966d7e0d4fcc69216b', // charley
        'pablo'   => '0d107d09f5bbe40cade3de5c71e9e9b7', // letmein
        'smithy'  => '5f4dcc3b5aa765d61d8327deb882cf99', // password
    ];
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, avatar = 'default.png' WHERE username = ?");
    foreach ($defaults as $username => $hash) {
        mysqli_stmt_bind_param($stmt, 'ss', $hash, $username);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    // Buku tamu (modul XSS Stored) -> hapus payload uji coba.
    mysqli_query($conn, 'DELETE FROM guestbook');
    mysqli_query($conn, "INSERT INTO guestbook (name, comment) VALUES ('Admin', 'Selamat datang di buku tamu WebSecLab!')");

    // Riwayat percobaan login (modul Brute Force level Impossible) -> buka kunci akun.
    mysqli_query($conn, 'DELETE FROM login_attempts');

    // Berkas hasil upload (modul File Upload) -> hapus kecuali .gitkeep.
    foreach (glob(rtrim($uploadDir, '/') . '/*') as $file) {
        if (is_file($file) && basename($file) !== '.gitkeep') {
            @unlink($file);
        }
    }
}

function module_intro(string $title, string $owasp, string $desc, array $hints = [], string $moduleKey = ''): void
{
    ?>
    <div class="mb-6">
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-2xl font-bold text-slate-900"><?= e($title) ?></h1>
            <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700"><?= e($owasp) ?></span>
            <?php if ($moduleKey !== ''): ?>
                <a href="<?= e(BASE_URL) ?>/edukasi.php?m=<?= e($moduleKey) ?>"
                   class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700 hover:bg-emerald-200">
                    📚 Pelajari Konsep Serangan
                </a>
            <?php endif; ?>
        </div>
        <p class="mt-2 text-sm text-slate-600"><?= e($desc) ?></p>
        <?php if ($hints): ?>
            <details class="mt-3 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm">
                <summary class="cursor-pointer font-medium text-slate-700">Petunjuk</summary>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-slate-600">
                    <?php foreach ($hints as $h): ?>
                        <li><?= e($h) ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>
    </div>
    <?php
}
