<?php
/**
 * WebSecLab - Konten Edukasi per modul kerentanan.
 *
 * Dipakai oleh edukasi.php untuk menampilkan penjelasan konsep serangan,
 * cara kerja, dan mitigasi di luar praktik eksploitasi langsung.
 *
 * Cara menambahkan video YouTube:
 * 1. Buka video YouTube pilihan Anda (cari video yang relevan dan materinya sudah
 *    Anda tonton/verifikasi sendiri, supaya kontennya dapat dipertanggungjawabkan).
 * 2. Ambil ID video dari URL, contoh: https://www.youtube.com/watch?v=XXXXXXXXXXX
 *    ID-nya adalah bagian setelah "v=", yaitu "XXXXXXXXXXX".
 * 3. Isi nilai 'youtube_id' di bawah dengan ID tersebut (antara tanda kutip).
 *    Biarkan string kosong '' kalau belum ada video untuk modul itu.
 */

return [
          'brute_force' => [
        'concept' => [
            'Brute force adalah teknik mencoba banyak kombinasi username dan password secara berurutan '
                . 'hingga menemukan kredensial yang benar. Penyerang biasanya memakai wordlist (daftar kata/password '
                . 'umum) dan alat otomatis seperti Hydra atau Burp Suite Intruder untuk mencoba ratusan hingga '
                . 'jutaan kombinasi dalam waktu singkat.',
            'Serangan ini berhasil karena banyak sistem login tidak membatasi jumlah percobaan, tidak memberi '
                . 'jeda antar percobaan, dan tidak mendeteksi pola percobaan yang mencurigakan.',
        ],
        'mitigation' => [
            'Terapkan rate limiting: batasi jumlah percobaan login dalam rentang waktu tertentu.',
            'Kunci akun sementara (account lockout) setelah beberapa kali gagal berturut-turut.',
            'Tambahkan CAPTCHA setelah beberapa kali percobaan gagal.',
            'Gunakan autentikasi dua faktor (2FA) sebagai lapisan keamanan tambahan.',
            'Catat (log) dan pantau percobaan login yang gagal untuk mendeteksi pola serangan.',
        ],
        'youtube_ids' => ['RV8Ro9Vzs8g', 'ttEXgEkKKOU', 'l_wVmVITefg'],
    ],

    'command_injection' => [
        'concept' => [
            'Command Injection terjadi ketika aplikasi meneruskan input pengguna ke perintah sistem operasi '
                . '(shell) tanpa validasi. Penyerang dapat menyisipkan karakter penghubung perintah seperti '
                . '`;`, `&&`, `||`, atau `|` untuk menjalankan perintah tambahan di luar yang dimaksudkan aplikasi.',
            'Contoh: fitur "ping alamat IP" yang menjalankan `ping ' . '127.0.0.1; cat /etc/passwd` akan menjalankan '
                . 'dua perintah sekaligus jika input tidak difilter, memungkinkan penyerang membaca file sensitif '
                . 'atau bahkan mengambil alih server.',
        ],
        'mitigation' => [
            'Hindari memanggil fungsi shell (shell_exec, exec, system) dengan input pengguna sama sekali jika memungkinkan.',
            'Jika terpaksa, gunakan escapeshellarg()/escapeshellcmd() dan validasi input dengan whitelist ketat (mis. hanya format IPv4).',
            'Jalankan proses dengan hak akses (privilege) serendah mungkin (principle of least privilege).',
            'Gunakan API bahasa pemrograman langsung (mis. fungsi jaringan native) daripada memanggil perintah shell eksternal.',
        ],
        'youtube_ids' => ['DL06TGmUCro'],
    ],

    'csrf' => [
        'concept' => [
            'Cross-Site Request Forgery (CSRF) menipu browser korban yang sedang login agar tanpa sadar '
                . 'mengirim permintaan (request) ke aplikasi target, misalnya mengubah password atau email. '
                . 'Serangan ini memanfaatkan fakta bahwa browser otomatis menyertakan cookie sesi di setiap '
                . 'permintaan ke domain yang sama.',
            'Contoh: penyerang membuat halaman jahat berisi form tersembunyi yang otomatis submit ke '
                . '`situs-korban.com/ubah-password`. Jika korban yang sedang login membuka halaman itu, '
                . 'passwordnya bisa berubah tanpa sepengetahuannya.',
        ],
        'mitigation' => [
            'Gunakan token anti-CSRF unik per sesi/per form, dan validasi token tersebut di server sebelum memproses permintaan.',
            'Minta konfirmasi ulang (re-authentication) untuk aksi sensitif seperti ubah password.',
            'Gunakan atribut cookie SameSite=Strict atau SameSite=Lax untuk membatasi pengiriman cookie lintas situs.',
            'Periksa header Origin/Referer sebagai lapisan tambahan (bukan satu-satunya pertahanan).',
        ],
        'youtube_ids' => ['PxBq8hMsb3A'],
    ],

    'file_upload' => [
        'concept' => [
            'Unrestricted File Upload terjadi ketika aplikasi mengizinkan pengguna mengunggah berkas tanpa '
                . 'membatasi jenis, ukuran, atau isi berkas tersebut. Penyerang dapat mengunggah web shell '
                . '(skrip PHP berbahaya) yang, jika berhasil diakses lewat browser, memberikan kontrol penuh '
                . 'atas server (Remote Code Execution).',
            'Validasi yang hanya mengandalkan ekstensi nama file atau tipe MIME dari browser mudah dipalsukan, '
                . 'karena kedua nilai itu dikirim oleh client dan bisa diubah bebas oleh penyerang.',
        ],
        'mitigation' => [
            'Validasi isi berkas (bukan hanya nama/ekstensi), misalnya dengan memeriksa signature file menggunakan getimagesize() untuk gambar.',
            'Simpan berkas dengan nama acak yang dibuat server, bukan nama asli dari pengguna.',
            'Simpan berkas di luar direktori yang bisa dieksekusi sebagai skrip, atau nonaktifkan eksekusi PHP di folder upload.',
            'Re-encode/proses ulang berkas (mis. resize gambar) untuk menghilangkan payload tersembunyi.',
            'Batasi ukuran berkas dan jumlah upload per pengguna.',
        ],
        'youtube_ids' => ['7xYI-KgkJF0'],
    ],

    'lfi' => [
        'concept' => [
            'Local File Inclusion (LFI) terjadi ketika aplikasi memuat berkas berdasarkan parameter yang '
                . 'dikontrol pengguna (mis. `?page=tentang.php`) tanpa validasi, menggunakan fungsi seperti '
                . '`include()` atau `require()`. Penyerang dapat mengganti parameter tersebut dengan path ke '
                . 'berkas sensitif di server, seperti `../../../../etc/passwd`.',
            'Jika server juga mengizinkan pemanggilan URL eksternal, variannya disebut Remote File Inclusion '
                . '(RFI), yang bisa langsung mengeksekusi kode dari server penyerang.',
        ],
        'mitigation' => [
            'Gunakan whitelist eksplisit: daftar nama berkas yang diizinkan, tolak semua nilai di luar daftar itu.',
            'Hindari menggunakan input pengguna secara langsung sebagai nama berkas/path.',
            'Nonaktifkan allow_url_include di konfigurasi PHP untuk mencegah RFI.',
            'Gunakan basename() dan validasi path untuk mencegah path traversal (../).',
        ],
        'youtube_ids' => ['LvaB_6rU-Cc', '485YJD48GAs'],
    ],

    'sql_injection' => [
        'concept' => [
            'SQL Injection terjadi ketika input pengguna digabungkan langsung ke dalam query SQL tanpa '
                . 'sanitasi, sehingga penyerang dapat mengubah struktur/makna query tersebut. Contoh klasik: '
                . 'input `\' OR 1=1 --` dapat membuat kondisi WHERE selalu bernilai benar, melewati proses login.',
            'Dengan teknik UNION SELECT, penyerang bahkan bisa mengekstrak data dari tabel lain di database '
                . 'yang sama, termasuk data sensitif seperti password pengguna.',
        ],
        'mitigation' => [
            'Gunakan prepared statement / parameterized query (mis. mysqli_prepare dengan bind_param) untuk SEMUA query yang melibatkan input pengguna.',
            'Terapkan prinsip least privilege pada akun database aplikasi (jangan pakai akun root untuk operasi harian).',
            'Validasi dan batasi tipe data input (mis. pastikan ID harus berupa angka).',
            'Jangan menampilkan pesan error database mentah ke pengguna (mencegah error-based SQL injection).',
        ],
        'youtube_ids' => ['Pohx-GidauE'],
    ],

    'xss_reflected' => [
        'concept' => [
            'Cross-Site Scripting (XSS) Reflected terjadi ketika input pengguna langsung ditampilkan kembali '
                . 'ke halaman HTML tanpa di-escape, sehingga skrip JavaScript yang disisipkan penyerang ikut '
                . 'dieksekusi oleh browser korban. Disebut "reflected" karena payload hanya aktif dalam satu '
                . 'kali permintaan (biasanya dikirim lewat link yang ditipu agar diklik korban).',
            'Skrip yang berhasil dieksekusi dapat mencuri cookie sesi, melakukan aksi atas nama korban, '
                . 'atau mengarahkan korban ke situs phishing.',
        ],
        'mitigation' => [
            'Escape semua output ke konteks HTML dengan htmlspecialchars() (gunakan ENT_QUOTES).',
            'Terapkan Content Security Policy (CSP) untuk membatasi sumber skrip yang boleh dijalankan browser.',
            'Jangan membuat filter berbasis blacklist kata kunci (mudah dilewati) — gunakan pendekatan escape output secara konsisten.',
            'Validasi input sesuai konteks penggunaannya (HTML, atribut, URL, JavaScript memerlukan escaping berbeda-beda).',
        ],
        'youtube_ids' => ['uOZEukG9p3Q'],
    ],

    'xss_stored' => [
        'concept' => [
            'Cross-Site Scripting (XSS) Stored mirip dengan XSS Reflected, tetapi payload JavaScript '
                . 'disimpan secara permanen di server (misalnya di database lewat kolom komentar atau buku tamu). '
                . 'Setiap kali halaman tersebut dibuka oleh pengguna lain, payload yang tersimpan ikut dieksekusi '
                . 'di browser mereka — membuat dampaknya jauh lebih luas dibanding XSS Reflected.',
            'Karena sifatnya persisten, XSS Stored berpotensi menyerang banyak korban sekaligus tanpa perlu '
                . 'mengirim link khusus ke tiap korban.',
        ],
        'mitigation' => [
            'Escape output setiap kali data ditampilkan ke HTML, meskipun data berasal dari database sendiri (jangan percaya data tersimpan).',
            'Sanitasi input sebelum disimpan sebagai lapisan tambahan (mis. strip_tags untuk kolom yang tidak butuh HTML).',
            'Terapkan Content Security Policy (CSP) sebagai pertahanan berlapis.',
            'Gunakan HttpOnly pada cookie sesi agar tidak bisa diakses lewat JavaScript, membatasi dampak jika XSS tetap terjadi.',
        ],
        'youtube_ids' => ['aOgKHzo9Rdw'],
    ],
];
