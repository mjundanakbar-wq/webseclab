# WebSecLab – Modern Vulnerable Web Application Platform

Laboratorium keamanan web interaktif berbasis **PHP Native + MySQL + Tailwind CSS (CDN)**, terinspirasi DVWA, dengan antarmuka modern.
Dibuat sebagai media edukasi, simulasi penetration testing, dan pembelajaran secure coding.

> **PERINGATAN:** aplikasi ini SENGAJA rentan. Jalankan hanya di localhost atau jaringan lab yang terisolasi.
> Jangan diunggah ke hosting publik. File `.htaccess` di root membatasi akses hanya dari komputer lokal (`Require local`).

## Instalasi (XAMPP)

1. Salin folder `webseclab/` ke `C:\xampp\htdocs\` (Linux: `/opt/lampp/htdocs/` atau `/var/www/html/`).
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, pilih tab **Import**, lalu impor file `database/webseclab.sql`
   (atau lewat terminal: `mysql -u root < database/webseclab.sql`).
4. Jika MySQL Anda memakai password, ubah kredensial di `config/database.php`.
5. Pastikan folder `uploads/` dapat ditulis oleh Apache (di Linux: `chmod 777 uploads`).
6. Buka `http://localhost/webseclab/` dan login.

Ekstensi PHP yang dibutuhkan: `mysqli`, `mysqlnd`, `gd` (untuk level Impossible modul File Upload). Semuanya aktif di XAMPP standar.

> **Catatan:** sejak versi ini, tampilan tidak lagi memuat Tailwind CSS dari CDN/internet.
> Semua gaya sudah dibundel lokal di `assets/css/app.css`, jadi WebSecLab tetap tampil normal
> dan cepat walau tanpa koneksi internet — penting untuk demo/presentasi di tempat dengan WiFi terbatas.

## Halaman Edukasi (Baru)

Setiap modul kerentanan punya tombol **📚 Pelajari Konsep Serangan** di bagian atas halaman, yang membuka halaman
edukasi berisi: penjelasan konsep & cara kerja serangan, cara mitigasi/pencegahan, dan slot video YouTube.

Video YouTube belum diisi secara default (supaya tidak menampilkan video yang tidak relevan/tidak terverifikasi).
Untuk menambahkan video pilihan Anda sendiri:
1. Buka `config/education_content.php`.
2. Cari modul yang ingin diisi (key: `brute_force`, `command_injection`, `csrf`, `file_upload`, `lfi`,
   `sql_injection`, `xss_reflected`, `xss_stored`).
3. Isi `'youtube_id' => '...'` dengan ID video dari URL YouTube (bagian setelah `v=`).

## Sebelum demo/presentasi

Hasil percobaan sebelumnya (password yang diubah lewat modul CSRF, akun yang terkunci di modul Brute Force,
payload yang tersimpan di modul XSS Stored, file di modul File Upload) **tersimpan permanen di database**
sampai direset. Supaya tidak muncul tak terduga saat demo (mis. popup `alert()` dari XSS lama, atau akun
`admin` tiba-tiba terkunci/password berubah):

1. Buka menu **Security Level**.
2. Klik tombol **Reset Data Lab** di bagian bawah halaman, lalu konfirmasi.
3. Ini akan: mengembalikan password semua akun ke default, membuka akun yang terkunci, mengosongkan buku tamu
   (XSS Stored), dan menghapus file hasil upload.
4. Atur Security Level kembali ke **Low** sebagai titik awal demo, lalu login ulang jika perlu.

Disarankan melakukan reset ini sesaat sebelum naik presentasi, setelah sesi latihan/uji coba selesai.

## Akun bawaan

| Username | Password |
|----------|----------|
| admin    | password |
| gordonb  | abc123   |
| 1337     | charley  |
| pablo    | letmein  |
| smithy   | password |

Password disimpan sebagai MD5 (disengaja agar hasil SQL Injection bisa dianalisis). Level Impossible pada modul CSRF
menyimpan password dengan `password_hash()`; aplikasi mengenali kedua format. Untuk mengembalikan data awal, impor ulang `database/webseclab.sql`.

## Security level

Diatur lewat menu **Security Level** (`$_SESSION['security_level']`) dan berlaku di semua modul.

| Level | Keterangan |
|-------|------------|
| Low | Tanpa filter, validasi, atau sanitasi. |
| Medium | Filter/blacklist dasar yang mudah dilewati. |
| High | Filter lebih ketat, tetapi masih ada celah teknis. |
| Impossible | Secure coding: prepared statement, validasi ketat, token anti-CSRF, escape output. |

## Modul

| # | File | Kerentanan |
|---|------|------------|
| 1 | `modules/brute_force.php` | Brute Force (tanpa rate-limiting) |
| 2 | `modules/command_injection.php` | Command Injection (fitur ping) |
| 3 | `modules/csrf.php` | CSRF (ubah password) |
| 4 | `modules/file_upload.php` | Unrestricted File Upload (foto profil) |
| 5 | `modules/lfi.php` | Local File Inclusion (`?page=file1.php`) |
| 6 | `modules/sql_injection.php` | SQL Injection (pencarian user) |
| 7 | `modules/xss_reflected.php` | XSS Reflected (sapaan nama) |
| 8 | `modules/xss_stored.php` | XSS Stored (buku tamu) |

### Cara kerja View Source

Setiap modul menandai kode tiap level dengan komentar penanda. Tombol **View Source** membaca file modul dan menampilkan blok
kode **yang benar-benar dieksekusi** pada level aktif, sehingga tampilan dan perilaku aplikasi selalu konsisten.
Saat menambah atau mengubah level, pertahankan pasangan penanda tersebut di dalam file modul.

## Catatan implementasi

- Ditambahkan satu tabel di luar PRD, `login_attempts`, untuk mekanisme penguncian akun pada level Impossible modul Brute Force.
- Login aplikasi (`login.php`) sendiri dibuat aman (prepared statement) agar lab tetap stabil saat dipakai.
- Modul File Upload menyimpan berkas ke `uploads/` tanpa pembatasan eksekusi pada level rendah. Itu bagian dari simulasi;
  bersihkan isi `uploads/` setelah praktik.
- Modul CSRF mengubah password akun yang sedang login. Impor ulang SQL untuk mengembalikan password default.
- Tailwind dimuat via CDN sehingga halaman memerlukan koneksi internet.

## Struktur direktori

```
webseclab/
├── config/database.php      # Koneksi MySQL, session, fungsi bantu
├── includes/header.php      # Layout atas + sidebar Tailwind
├── includes/footer.php
├── modules/                 # 8 modul kerentanan
├── pages/                   # Halaman target modul LFI
├── database/webseclab.sql   # Skema + data awal
├── uploads/                 # Penyimpanan upload foto profil
├── index.php  login.php  logout.php  security.php
└── .htaccess                # Batasi akses ke localhost
```
