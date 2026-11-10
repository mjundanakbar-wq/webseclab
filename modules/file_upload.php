<?php
require_once __DIR__ . '/../config/database.php';
require_login();

$level = security_level();
$page_title = 'File Upload';
$active = 'file_upload';

$upload_dir = __DIR__ . '/../uploads/';
$msg = '';
$msgType = 'info';
$uploaded = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Upload'])) {
    if (!isset($_FILES['uploaded']) || $_FILES['uploaded']['error'] !== UPLOAD_ERR_OK) {
        $msg = 'Upload gagal. Pilih file terlebih dahulu (kode error: ' . (int) ($_FILES['uploaded']['error'] ?? -1) . ').';
        $msgType = 'error';
    } else {
        switch ($level) {
            case 'low':
                // [LEVEL:low]
                $name = basename($_FILES['uploaded']['name']);

                // Tidak ada pemeriksaan tipe, ekstensi, maupun isi berkas.
                if (move_uploaded_file($_FILES['uploaded']['tmp_name'], $upload_dir . $name)) {
                    $uploaded = $name;
                }
                // [END:low]
                break;

            case 'medium':
                // [LEVEL:medium]
                $name = basename($_FILES['uploaded']['name']);
                $type = $_FILES['uploaded']['type'];   // Nilai ini dikirim oleh client, bukan hasil deteksi server.
                $size = $_FILES['uploaded']['size'];

                if (($type === 'image/jpeg' || $type === 'image/png') && $size < 500000) {
                    if (move_uploaded_file($_FILES['uploaded']['tmp_name'], $upload_dir . $name)) {
                        $uploaded = $name;
                    }
                } else {
                    $msg = 'Hanya gambar JPEG/PNG di bawah 500 KB yang diperbolehkan.';
                    $msgType = 'error';
                }
                // [END:medium]
                break;

            case 'high':
                // [LEVEL:high]
                $name = basename($_FILES['uploaded']['name']);
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $size = $_FILES['uploaded']['size'];

                // Ekstensi + getimagesize(), namun nama asli tetap dipakai dan isi berkas tidak dibersihkan.
                if (in_array($ext, ['jpg', 'jpeg', 'png'], true)
                    && $size < 500000
                    && @getimagesize($_FILES['uploaded']['tmp_name'])) {
                    if (move_uploaded_file($_FILES['uploaded']['tmp_name'], $upload_dir . $name)) {
                        $uploaded = $name;
                    }
                } else {
                    $msg = 'Berkas bukan gambar JPG/PNG yang valid.';
                    $msgType = 'error';
                }
                // [END:high]
                break;

            case 'impossible':
                // [LEVEL:impossible]
                if (!check_token($_POST['user_token'] ?? '')) {
                    $msg = 'Token CSRF tidak valid.';
                    $msgType = 'error';
                    break;
                }

                $tmp  = $_FILES['uploaded']['tmp_name'];
                $info = @getimagesize($tmp);                       // Deteksi tipe dari isi berkas
                $map  = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'];

                if (!$info || !isset($map[$info[2]]) || $_FILES['uploaded']['size'] >= 500000
                    || !function_exists('imagecreatefromjpeg')) {
                    $msg = 'Berkas harus gambar JPG/PNG valid (< 500 KB) dan ekstensi GD harus aktif.';
                    $msgType = 'error';
                    break;
                }

                // Re-encode gambar: membuang metadata/payload tersembunyi.
                $img = ($info[2] === IMAGETYPE_JPEG) ? imagecreatefromjpeg($tmp) : imagecreatefrompng($tmp);
                if ($img) {
                    // Nama acak + ekstensi ditentukan server, bukan oleh pengguna.
                    $name = bin2hex(random_bytes(8)) . '.' . $map[$info[2]];
                    $ok = ($info[2] === IMAGETYPE_JPEG)
                        ? imagejpeg($img, $upload_dir . $name, 90)
                        : imagepng($img, $upload_dir . $name);
                    imagedestroy($img);
                    if ($ok) {
                        $uploaded = $name;
                    }
                }
                // [END:impossible]
                break;
        }

        if ($uploaded !== null) {
            $stmt = mysqli_prepare($conn, 'UPDATE users SET avatar = ? WHERE user_id = ?');
            $uid = (int) $_SESSION['user_id'];
            mysqli_stmt_bind_param($stmt, 'si', $uploaded, $uid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $msg = 'Foto profil berhasil diunggah.';
            $msgType = 'success';
        } elseif ($msg === '') {
            $msg = 'Gagal menyimpan berkas.';
            $msgType = 'error';
        }
    }
}

$token = generate_token();

include __DIR__ . '/../includes/header.php';

module_intro(
    'Unrestricted File Upload',
    'A04 – Insecure Design',
    'Simulasi pengunggahan foto profil. Server yang tidak membatasi jenis berkas dapat menerima berkas berbahaya.',
    [
        'Mulailah dengan mengunggah gambar normal, lalu amati di mana berkas disimpan dan bagaimana ia diakses.',
        'Pelajari apa yang diperiksa server pada tiap level: tipe dari client, ekstensi, atau isi berkas.',
        'Level Impossible memakai nama acak dan re-encode gambar.',
    ],
    'file_upload'
);

if ($msg !== '') {
    ui_alert($msgType, $msg);
}
if ($uploaded !== null) {
    echo '<p class="mt-3 text-sm text-slate-600">Tersimpan di: <a class="font-mono text-indigo-600 hover:underline" target="_blank" href="'
        . e(BASE_URL . '/uploads/' . $uploaded) . '">uploads/' . e($uploaded) . '</a></p>';
}
?>

<form method="post" enctype="multipart/form-data" class="mt-4 max-w-md rounded-xl border border-slate-200 bg-white p-6">
    <input type="hidden" name="user_token" value="<?= e($token) ?>">
    <label class="mb-1 block text-sm font-medium text-slate-700" for="uploaded">Pilih foto profil</label>
    <input id="uploaded" name="uploaded" type="file"
           class="mb-5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
    <button type="submit" name="Upload" value="Upload" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Upload</button>
</form>

<?php
view_source_block(__FILE__);
include __DIR__ . '/../includes/footer.php';
