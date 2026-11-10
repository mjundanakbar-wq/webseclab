<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'SQL Injection';
$active = 'sql_injection';

$q = $_GET['q'] ?? '';
$searched = false;
$rows = [];
$error = '';
$query = '';

if (isset($_GET['Search']) && $q !== '') {
    $searched = true;

    switch ($level) {
        case 'low':
            // [LEVEL:low]
            // Input langsung dirangkai ke dalam query SQL.
            $query = "SELECT user_id, username, avatar FROM users WHERE user_id = '$q' OR username LIKE '%$q%'";
            $res = mysqli_query($conn, $query);
            // [END:low]
            break;

        case 'medium':
            // [LEVEL:medium]
            // Blacklist kata kunci: dihapus satu kali saja, tidak rekursif.
            $q = str_ireplace(['union', 'select', '--', '#'], '', $q);

            $query = "SELECT user_id, username, avatar FROM users WHERE user_id = '$q' OR username LIKE '%$q%'";
            $res = mysqli_query($conn, $query);
            // [END:medium]
            break;

        case 'high':
            // [LEVEL:high]
            // Pencarian hanya berdasarkan ID. Pola serangan umum diblokir, tetapi input tidak
            // di-cast ke integer dan tidak memakai prepared statement.
            if (preg_match('/union\s+select|--|#|;/i', $q)) {
                $error = 'Input terdeteksi mencurigakan.';
                break;
            }
            $query = "SELECT user_id, username, avatar FROM users WHERE user_id = $q";
            $res = mysqli_query($conn, $query);
            // [END:high]
            break;

        case 'impossible':
            // [LEVEL:impossible]
            if (!check_token($_GET['user_token'] ?? '')) {
                $error = 'Token CSRF tidak valid.';
                break;
            }

            // Prepared statement: data dan perintah SQL dipisahkan.
            $id   = ctype_digit($q) ? (int) $q : 0;
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $stmt = mysqli_prepare($conn, 'SELECT user_id, username, avatar FROM users WHERE user_id = ? OR username LIKE ?');
            mysqli_stmt_bind_param($stmt, 'is', $id, $like);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            // [END:impossible]
            break;
    }

    if (isset($res)) {
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $rows[] = $r;
            }
        } elseif ($level === 'impossible') {
            $error = 'Terjadi kesalahan saat memproses permintaan.';
        } else {
            // Pesan error database ditampilkan apa adanya (membantu error-based SQLi).
            $error = 'Error database: ' . mysqli_error($conn);
        }
    }
}

$token = generate_token();

include __DIR__ . '/../includes/header.php';

module_intro(
    'SQL Injection (SQLi)',
    'A03 – Injection',
    'Simulasi pencarian data pengguna berdasarkan ID atau username. Input yang dirangkai langsung ke query SQL memungkinkan penyerang mengubah makna query.',
    [
        'Coba ID normal (1) lalu username (admin) untuk memahami perilaku normal.',
        "Uji payload klasik seperti ' OR 1=1 -- dan ekstraksi data dengan UNION SELECT (tabel users memiliki 3 kolom pada query ini).",
        'Level High hanya mencari berdasarkan ID. Perhatikan konteks angka pada query-nya.',
    ],
    'sql_injection'
);
?>

<form method="get" class="max-w-md rounded-xl border border-slate-200 bg-white p-6">
    <input type="hidden" name="user_token" value="<?= e($token) ?>">
    <label class="mb-1 block text-sm font-medium text-slate-700" for="q">User ID atau Username</label>
    <input id="q" name="q" type="text" value="" autocomplete="off"
           class="mb-5 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    <button type="submit" name="Search" value="Search" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Cari</button>
</form>

<?php
if ($error !== '') {
    ui_alert('error', $error);
}

if ($searched && $query !== '' && $level !== 'impossible') {
    echo '<p class="mt-4 text-xs text-slate-500">Query dieksekusi:</p>';
    echo '<pre class="mt-1 overflow-x-auto rounded-lg bg-slate-900 p-3 text-xs text-amber-300">' . e($query) . '</pre>';
}

if ($searched && $error === '') {
    if ($rows) {
        echo '<div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white"><table class="w-full text-left text-sm">';
        echo '<thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-4 py-2">ID</th><th class="px-4 py-2">Username</th><th class="px-4 py-2">Avatar</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr class="border-t border-slate-100"><td class="px-4 py-2">' . e($r['user_id']) . '</td><td class="px-4 py-2">'
                . e($r['username']) . '</td><td class="px-4 py-2 font-mono text-xs">' . e($r['avatar']) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    } else {
        ui_alert('info', 'Tidak ada data yang cocok.');
    }
}

view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
