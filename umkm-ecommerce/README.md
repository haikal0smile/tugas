# Toko UMKM — Website E-commerce (PHP + MySQL)

Aplikasi web e-commerce untuk UMKM sesuai spesifikasi tugas Rekayasa E-Bisnis:
frontend pemesanan pelanggan + backend admin dengan Inventaris, Logistik, dan
Keuangan yang saling tersinkron otomatis, lengkap dengan notifikasi pesanan
masuk secara real-time.

## Fitur

**Sisi Pelanggan (Frontend)**
- Beranda dengan banner promosi, katalog produk, pencarian & filter kategori
- Detail produk, keranjang belanja, checkout (COD / Transfer, Diantar / Ambil Sendiri)
- Register & Login, riwayat pesanan pelanggan

**Sisi Admin (Backend / ERP)**
- Dashboard: total pesanan, pendapatan, produk stok menipis
- Kelola pesanan & ubah status (pending → confirmed → processing → shipped → completed)
- Kelola produk & stok (restock manual tercatat di riwayat stok)
- Laporan keuangan (tersinkron otomatis dari setiap transaksi)
- **Notifikasi real-time** (bel di pojok kanan atas): muncul otomatis saat ada
  pesanan baru masuk atau stok produk menipis (polling setiap 8 detik, tanpa reload)

**Sinkronisasi Otomatis (inti tugas modul ERP)**
- Saat pelanggan checkout → stok produk otomatis berkurang (`adjustStock()`),
  transaksi keuangan tercatat (`recordFinance()`), notifikasi admin dibuat
  — semuanya dalam satu database transaction agar konsisten.
- Saat admin membatalkan pesanan → stok otomatis dikembalikan.
- Saat stok produk ≤ batas minimum → notifikasi "stok menipis" otomatis muncul.

## Cara Menjalankan (XAMPP / Laragon)

1. Salin folder `umkm-ecommerce` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka phpMyAdmin, buat database baru, lalu import file `database/schema.sql`
   (database `umkm_ecommerce` akan otomatis dibuat oleh file ini).
3. Sesuaikan kredensial di `config/database.php` jika perlu (default: host
   `localhost`, user `root`, password kosong).
4. Sesuaikan `BASE_URL` di `config/config.php` jika nama foldernya berbeda.
5. Buka `http://localhost/umkm-ecommerce/index.php` di browser.
6. Daftar akun baru lewat halaman Register, lalu jadikan admin lewat SQL:
   ```sql
   UPDATE users SET role='admin' WHERE email='email_anda@...';
   ```
7. Login ulang dengan akun tersebut → akan otomatis masuk ke Dashboard Admin.

## Struktur Folder

```
umkm-ecommerce/
├── admin/              # Panel admin (dashboard, pesanan, produk, keuangan)
├── api/                # Endpoint notifikasi (polling AJAX)
├── assets/             # CSS, JS, gambar produk (assets/img/)
├── config/             # Koneksi database & konfigurasi
├── database/schema.sql # Skema database + data contoh
├── includes/           # Header/footer & fungsi bantuan bersama
├── process/            # Handler form (cart, checkout, stok, dll)
└── *.php               # Halaman frontend pelanggan
```

## Menambahkan Gambar Produk

Unggah file gambar ke `assets/img/`, lalu isi nama filenya (mis. `keripik.jpg`)
di kolom "Nama File Gambar" saat menambah/edit produk lewat panel admin.

## Catatan

- Framework: PHP native (vanilla) + PDO, tanpa framework eksternal —
  mudah dijelaskan di laporan Bab 6 (Pengembangan Website E-commerce).
  Boleh diadaptasi ke framework (Laravel/CodeIgniter) bila kelompok memilih itu.
- Source code ini siap diupload ke GitHub sesuai instruksi "Output Tugas".
