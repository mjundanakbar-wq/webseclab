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
            'Serangan brute force adalah metode peretasan yang menggunakan metode coba-coba untuk membobol kata sandi, kredensial login , dan kunci enkripsi. Ini adalah taktik sederhana namun andal untuk mendapatkan akses tidak sah ke akun individu dan sistem serta jaringan organisasi. Peretas mencoba berbagai nama pengguna dan kata sandi, seringkali menggunakan komputer untuk menguji berbagai kombinasi, hingga mereka menemukan informasi login yang benar.',
            'Serangan ini berhasil karena banyak sistem login tidak membatasi jumlah percobaan, tidak memberi '
                . 'jeda antar percobaan, dan tidak mendeteksi pola percobaan yang mencurigakan.',
        ],
        'mitigation' => [
            'Buat kata sandi yang kuat dan terdiri dari banyak karakter : Aturan dasar yang perlu diingat adalah kata sandi harus lebih dari 10 karakter dan mencakup huruf besar dan kecil, simbol, dan angka.',
            'Gunakan frasa sandi yang rumit : Frasa sandi adalah gabungan beberapa kata atau segmen dengan karakter khusus yang membuatnya lebih sulit ditebak.',
            'Buat aturan pembuatan kata sandi : Taktik kata sandi bagus lainnya adalah memangkas kata-kata sehingga tampak tidak masuk akal bagi orang lain yang membacanya. Misalnya, mempersingkat kata "hope" menjadi "hp" atau "blue" menjadi "bl".',
            'Manfaatkan pengelola kata sandi (password manager) : Gunakan aplikasi ini untuk membuat dan menyimpan kata sandi yang panjang, kompleks, dan unik secara otomatis tanpa takut lupa.',
            'Gunakan kata sandi unik: Bedakan kata sandi untuk setiap akun guna mencegah peretasan berantai (credential stuffing) jika salah satu situs mengalami kebocoran data.',
        ],
        'youtube_ids' => ['RV8Ro9Vzs8g', 'ttEXgEkKKOU', 'l_wVmVITefg'],
        'sources' => [
        ['title' => 'Fortinet - Apa itu Serangan Brute Force?', 'url' => 'https://www.fortinet.com/resources/cyberglossary/brute-force-attack'],
        ],
    ],
    

    'command_injection' => [
        'concept' => [
            'Injeksi perintah OS (Command Injection) adalah celah keamanan yang memungkinkan penyerang menjalankan perintah sistem operasi secara bebas di server aplikasi, sehingga berisiko membahayakan seluruh data dan sistem yang terhubung.',
            'Terjadi saat aplikasi meneruskan input pengguna ke perintah shell tanpa validasi. Penyerang menyisipkan karakter pemisah perintah (seperti &, &&, |, atau ;) agar server ikut mengeksekusi perintah tambahan yang disisipkan.',
        ],
        'mitigation' => [
            'Jangan panggil perintah OS langsung : Gunakan fitur atau API bawaan bahasa pemrograman yang lebih aman daripada menjalankan perintah sistem operasi.',
            'Validasi input dengan ketat: Jika terpaksa menggunakan input pengguna, batasi hanya pada daftar nilai yang diizinkan, angka, atau huruf dan angka (alfanumerik) tanpa spasi maupun simbol.',
            'Hindari metode escaping: Jangan hanya menyaring karakter khusus shell, karena cara ini rawan salah dan mudah ditembus penyerang.',
        ],
        'youtube_ids' => ['DL06TGmUCro'],
        'sources' => [
        ['title' => 'PortSwigger - OS Command Injection', 'url' => 'https://portswigger.net/web-security/os-command-injection'],
        ],
    ],

    'csrf' => [
        'concept' => [
            'Cross-Site Request Forgery (CSRF) adalah celah keamanan yang memaksa pengguna melakukan tindakan yang tidak diinginkan pada aplikasi web tempat mereka sedang login (seperti mengubah email, kata sandi, atau transfer dana), yang berisiko menyebabkan pengambilalihan akun penuh.',
            'Serangan ini terjadi jika terdapat aksi sensitif yang hanya mengandalkan cookie sesi (atau kredensial otomatis lain) tanpa parameter rahasia tambahan yang sulit ditebak. Karena browser otomatis menyertakan cookie sesi ke domain terkait, penyerang dapat memicu permintaan HTTP tanpa sepengetahuan korban',
            'Contoh: Penyerang membuat halaman web berisi form HTML tersembunyi yang otomatis mengirim permintaan POST ke /email/change (atau lewat URL GET). Saat korban yang sedang login membuka tautan jebakan tersebut, browser otomatis mengirimkan cookie sesinya sehingga email korban langsung berubah.',
        ],
        'mitigation' => [
            'Gunakan dan validasi Token CSRF : Buat token yang acak, sulit ditebak (menggunakan CSPRNG), dan terikat pada sesi pengguna. Kirimkan melalui hidden field pada form POST (bukan lewat URL atau cookie) lalu wajib validasi kecocokannya di server sebelum memproses permintaan.',
            'Terapkan pembatasan cookie SameSite yang ketat : Atur atribut SameSite=Strict secara default pada setiap cookie (atau Lax jika memang diperlukan, serta hindari None) untuk membatasi pengiriman cookie dari situs lain.',
            'Isolasi konten yang tidak aman : Pisahkan konten atau file unggahan pengguna pada domain/situs yang berbeda dari fungsi atau data sensitif, karena kebijakan SameSite tidak melindungi dari serangan lintas asal yang berasal dari situs yang sama (same-site).',

        ],
        'youtube_ids' => ['PxBq8hMsb3A'],
        'sources' => [
        ['title' => 'PortSwigger - What is CSRF?', 'url' => 'https://portswigger.net/web-security/csrf'],
        ],

    ],

    'file_upload' => [
        'concept' => [
            'Kerentanan unggahan file (File Upload Vulnerability) terjadi saat server web mengizinkan pengguna mengunggah file tanpa validasi memadai terhadap nama, tipe, isi, atau ukurannya. Akibatnya, penyerang dapat mengunggah file berbahaya seperti skrip sisi server (web shell) untuk mengambil alih kendali server (Remote Code Execution).',
            'Kerentanan muncul ketika validasi file cacat atau mudah dilewati—misalnya hanya memeriksa header Content-Type, mengandalkan daftar hitam (blacklist) ekstensi yang bisa diakali (seperti .php5 atau pengaburan nama), hingga celah race condition sebelum file dihapus. Jika server dikonfigurasi untuk mengeksekusi file tersebut, penyerang cukup mengakses URL file yang diunggah untuk menjalankan perintah jarak jauh.',
        ],
        'mitigation' => [
            'Gunakan whitelist ekstensi : Periksa ekstensi file hanya terhadap daftar yang diizinkan (whitelist), bukan memblokir ekstensi tertentu (blacklist).',
            'Cegah path traversal : Pastikan nama file tidak mengandung karakter direktori atau urutan penelusuran seperti ../.',
            'Ganti nama file: Ubah nama file yang diunggah secara acak agar tidak menimpa file penting yang sudah ada di server.',
            'Validasi di folder sementara: Jangan langsung menyimpan file ke sistem file permanen sebelum seluruh proses validasi selesai dan dinyatakan aman.',
            'Gunakan framework yang mapan: Manfaatkan pustaka atau kerangka kerja tepercaya untuk memproses unggahan file daripada membuat mekanisme validasi manual sendiri.',
        ],
        'youtube_ids' => ['7xYI-KgkJF0'],
        'sources' => [
        ['title' => 'PortSwigger – File Upload Vulnerabilities', 'url' => 'https://portswigger.net/web-security/file-upload'],
        ],

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
