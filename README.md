# Lisensi MGX

**Lisensi MGX** adalah sistem manajemen lisensi berbasis web yang dirancang untuk membantu developer dalam mengelola lisensi software mereka. Proyek ini memungkinkan verifikasi lisensi secara otomatis melalui antarmuka pengguna dan endpoint API yang siap diintegrasikan dengan aplikasi Anda.

**Username** : *rezadkim*
<br>
**Password** : *demomgx*

> Demo & dokumentasi API: [https://demomgx.kitsunexploit.com/pages/dokumentasi.php](https://demomgx.kitsunexploit.com/pages/dokumentasi.php)

---

## 🔥 Fitur Unggulan

- **Manajemen Lisensi Lengkap** “ Tambah, edit, dan hapus lisensi dengan mudah melalui antarmuka web.
- **Verifikasi Otomatis via API** “ Validasi lisensi secara real-time dari aplikasi klien.
- **Contoh Implementasi API** “ Tersedia dalam berbagai bahasa, termasuk Python.
- **Antarmuka Modern & Responsif** “ UI yang bersih, ringan, dan ramah pengguna.
- **Database SQL** “ Menggunakan MySQL sebagai penyimpanan utama.

---

## 📋 Struktur Direktori

```
Lisensi-MGX/
 api/              # Endpoint API (verifikasi lisensi, dsb)
 assets/           # File statis seperti CSS, JS, gambar
 exampleapi/       # Contoh implementasi API (Python)
 komponen/         # Komponen modular aplikasi
 pages/            # Halaman utama (dashboard, login, dll)
 config.php        # File konfigurasi dan koneksi database
 index.php         # Entry point aplikasi
```

---

## 🖥️ Teknologi yang Digunakan

| Teknologi     | Fungsi                            |
|---------------|------------------------------------|
| **PHP**       | Backend dan logika aplikasi        |
| **JavaScript**| Interaktivitas sisi klien          |
| **Python**    | Contoh implementasi API (opsional) |
| **HTML/CSS**  | Desain dan layout antarmuka        |
| **MySQL**     | Database untuk menyimpan lisensi   |

---

## 📝 Cara Instalasi

1. **Clone Repositori:**

   ```bash
   git clone https://github.com/rezadkim/Lisensi-MGX.git
   ```

2. **Konfigurasi Database:**

   - Buat database MySQL baru
   - Import file SQL jika tersedia (atau buat skema manual sesuai struktur aplikasi)

3. **Edit `config.php`:**

   ```php
   $host = "localhost";
   $user = "user_mysql_anda";
   $pass = "password_mysql_anda";
   $db   = "nama_database";
   ```

4. **Jalankan Aplikasi:**

   - Pastikan PHP dan MySQL aktif
   - Akses melalui browser: `http://localhost/Lisensi-MGX`

---

## 📖 Panduan Penggunaan

Untuk informasi lengkap terkait dokumentasi API dan demo penggunaan, silakan kunjungi:

[https://demomgx.kitsunexploit.com/pages/dokumentasi.php](https://demomgx.kitsunexploit.com/pages/dokumentasi.php)

---

## ⛓️‍💥 Kontribusi

Kontribusi sangat terbuka!

1. Fork repositori ini
2. Buat branch baru: `git checkout -b fitur-anda`
3. Commit perubahan: `git commit -m 'Menambahkan fitur baru'`
4. Push ke branch: `git push origin fitur-anda`
5. Buka Pull Request

---

## 🛡️ Lisensi

Proyek ini dilisensikan di bawah [ "Proyek privat / tidak untuk digunakan ulang tanpa izin"].

---

## 🔔 Kontak

Untuk pertanyaan atau kerja sama, silakan hubungi langsung melalui pesan pribadi.
