<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'Command Injection';
$active = 'command_injection';

$output = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Submit'])) {
    switch ($level) {
        case 'low':
            // [LEVEL:low]
            $ping   = stristr(php_uname('s'), 'Windows') ? 'ping ' : 'ping -c 4 ';
            $target = $_POST['ip'];

            // Input langsung digabung ke perintah shell.
            $output = shell_exec($ping . $target);
            // [END:low]
            break;

        case 'medium':
            // [LEVEL:medium]
            $ping   = stristr(php_uname('s'), 'Windows') ? 'ping ' : 'ping -c 4 ';
            $target = $_POST['ip'];

            // Blacklist sederhana: hanya dua pola yang dihapus.
            $target = str_replace(['&&', ';'], '', $target);

            $output = shell_exec($ping . $target);
            // [END:medium]
            break;

        case 'high':
            // [LEVEL:high]
            $ping   = stristr(php_uname('s'), 'Windows') ? 'ping ' : 'ping -c 4 ';
            $target = trim($_POST['ip']);

            // Blacklist lebih panjang, tetapi masih ada celah (perhatikan pola '| ').
            $blacklist = [
                '&'  => '',
                ';'  => '',
                '| ' => '',
                '-'  => '',
                '$'  => '',
                '('  => '',
                ')'  => '',
                '`'  => '',
                '||' => '',
            ];
            $target = str_replace(array_keys($blacklist), $blacklist, $target);

            $output = shell_exec($ping . $target);
            // [END:high]
            break;

        case 'impossible':
            // [LEVEL:impossible]
            if (!check_token($_POST['user_token'] ?? '')) {
                $error = 'Token CSRF tidak valid.';
                break;
            }
            $ping   = stristr(php_uname('s'), 'Windows') ? 'ping ' : 'ping -c 4 ';
            $octets = explode('.', trim($_POST['ip'] ?? ''));

            // Whitelist: harus tepat 4 oktet angka 0-255 (IPv4).
            $valid = count($octets) === 4;
            foreach ($octets as $o) {
                if (!ctype_digit($o) || (int) $o > 255) {
                    $valid = false;
                }
            }

            if ($valid) {
                $target = implode('.', array_map('intval', $octets));
                $output = shell_exec($ping . escapeshellarg($target));
            } else {
                $error = 'Masukkan alamat IPv4 yang valid.';
            }
            // [END:impossible]
            break;
    }
}

$token = generate_token();

include __DIR__ . '/../includes/header.php';

module_intro(
    'Command Injection',
    'A03 – Injection',
    'Simulasi pemeriksaan konektivitas IP address (ping). Input pengguna diteruskan ke perintah sistem operasi.',
    [
        'Masukkan IP valid, mis. 127.0.0.1, lalu coba sambungkan perintah lain dengan karakter chaining (;  &&  |).',
        'Pelajari karakter apa saja yang diblokir pada level Medium dan High.',
    ],
    'command_injection'
);
?>

<form method="post" class="max-w-md rounded-xl border border-slate-200 bg-white p-6">
    <input type="hidden" name="user_token" value="<?= e($token) ?>">
    <label class="mb-1 block text-sm font-medium text-slate-700" for="ip">Alamat IP</label>
    <input id="ip" name="ip" type="text" placeholder="127.0.0.1"
           class="mb-5 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    <button type="submit" name="Submit" value="Submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Ping</button>
</form>

<?php
if ($error !== '') {
    ui_alert('error', $error);
}
if ($output !== null) {
    echo '<pre class="mt-4 overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-green-300">' . e($output === '' ? '(tidak ada output)' : $output) . '</pre>';
}

view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
