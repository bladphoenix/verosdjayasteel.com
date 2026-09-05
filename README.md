# Veros Djaya Steel — Website Jual Kandang Ayam

Website company profile + katalog produk kandang ayam baja galvanis, lengkap dengan
panel admin (CRUD produk, kategori, dan pengaturan situs). Dibangun dengan **PHP + MySQL (PDO)**,
tanpa framework, mudah dihosting di cPanel/shared hosting.

## Fitur

- **Frontend**
  - Banner **"Website ini DIJUAL"** di atas semua halaman — tombol gabung (split button)
    **Pesan via WhatsApp + Telegram**, ringkas satu baris di mobile, bisa ditutup pengunjung.
  - Beranda: hero, keunggulan, kategori, produk unggulan, tentang, CTA kontak.
  - Halaman katalog (`products.php`) dengan filter kategori + pencarian.
  - **Halaman detail produk** (`product.php`) — spesifikasi lengkap, deskripsi, produk terkait,
    dan tombol **Pesan via WhatsApp** & **Chat Telegram**.
  - Tombol WhatsApp mengambang di semua halaman.
- **Admin panel** (`/admin`)
  - Login aman (password ter-hash `bcrypt`, proteksi sesi di setiap halaman).
  - CRUD **Produk** (upload + kompres gambar otomatis, status stok, produk unggulan).
  - CRUD **Kategori**.
  - **Pengaturan** situs: nomor WhatsApp/Telegram, kontak, teks hero & tentang — semua bisa diubah tanpa ngoding.

## Kontak (default)

- WhatsApp: <https://wa.me/62859191749378>
- Telegram: <https://t.me/+6287821381136>

Ubah kapan saja lewat **Admin → Pengaturan**.

## Cara Install

1. **Buat database** MySQL (mis. `verosdjayasteel`) di phpMyAdmin/cPanel.
2. **Import** file `database.sql` ke database tersebut.
3. **Sesuaikan koneksi** di `admin/config/db.php` (`$db`, `$user`, `$pass`).
4. Upload semua file ke web root (`public_html`).
5. **Buat akun admin** (tidak ada password default demi keamanan):
   - Edit `admin/setup.php`, ubah `$ADMIN_PASS` menjadi password kuat Anda.
   - Buka `https://domain-anda.com/admin/setup.php` di browser (hanya jalan saat belum ada admin).
   - Setelah akun dibuat, **HAPUS `admin/setup.php`**.
6. Login di `https://domain-anda.com/admin/login.php`.

### Keamanan yang sudah diterapkan
- Password admin di-hash `bcrypt`; sesi diperketat (HttpOnly, SameSite=Lax, Secure saat HTTPS).
- Proteksi **CSRF** (token) di semua aksi admin (tambah/edit/hapus/pengaturan).
- Semua query memakai **prepared statement** (anti SQL injection); output di-escape (anti XSS).
- Folder `assets/uploads/` menolak eksekusi skrip; `setup.php` hanya jalan saat belum ada admin.

## Struktur

```
/                     index.php, products.php, product.php, style.css, script.js
/inc                  functions.php, header.php, footer.php   (partial frontend + helper)
/assets/uploads       gambar produk (writable)
/admin                login, dashboard, CRUD produk/kategori, settings
/admin/config/db.php  konfigurasi database
/admin/includes       auth.php, header.php, footer.php        (kerangka admin)
/admin/assets         admin.css
database.sql          skema + data contoh
```

## Catatan

- Folder `assets/uploads/` harus dapat ditulis (permission 755/775) agar upload gambar berhasil.
- Deskripsi produk mendukung HTML sederhana (paragraf, tebal, daftar) — hanya diisi oleh admin tepercaya.
