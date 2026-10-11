# SIPUS-Del (Sistem Informasi Perpustakaan Kampus Del)

> **Modul Praktikum Minggu 05 — Pemrograman & Pengujian Web (12S3101)**  
> **Institut Teknologi Del**

---

## 1. Deskripsi Singkat
Aplikasi Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del) berbasis web menggunakan **Laravel 11.x** dengan pola arsitektur **Model-View-Controller (MVC)**, relasi **Eloquent ORM** (Kategori & Buku), validasi server-side, Blade component engine bertema Del Royal Purple (`#4C1D95`), serta optimasi *Eager Loading*.

---

## 2. Prasyarat (Prerequisites)
* PHP 8.2 atau lebih baru (ekstensi `pdo_sqlite` aktif)
* Composer 2.6+
* Database: SQLite (default) / MySQL

---

## 3. Panduan Instalasi & Menjalankan Proyek

1. **Clone repositori:**
   ```bash
   git clone https://github.com/<username>/ppw-2026-week5-<NIM>.git
   cd ppw-2026-week5-<NIM>
   ```

2. **Pasang dependensi Composer:**
   ```bash
   composer install
   ```

3. **Salin berkas konfigurasi lingkungan:**
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan migrasi tabel dan seeder data:**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Jalankan server aplikasi:**
   ```bash
   php artisan serve
   ```
   Buka peramban di: **[http://localhost:8000](http://localhost:8000)**

---

## 4. Daftar Fitur & Routing (RESTful Resource)

| Metode | URL / Rute | Nama Rute | Keterangan |
| :--- | :--- | :--- | :--- |
| `GET` | `/` | - | Redirect ke `/buku` |
| `GET` | `/buku` | `buku.index` | Daftar inventaris buku & pagination |
| `GET` | `/buku/create` | `buku.create` | Formulir tambah buku baru |
| `POST` | `/buku` | `buku.store` | Simpan buku baru ke database |
| `GET` | `/buku/{buku}` | `buku.show` | Detail buku & status ketersediaan |
| `GET` | `/buku/{buku}/edit` | `buku.edit` | Formulir edit data buku |
| `PUT` | `/buku/{buku}` | `buku.update` | Perbarui data buku |
| `DELETE` | `/buku/{buku}` | `buku.destroy` | Hapus data buku |
| `GET` | `/kategori` | `kategori.index` | Daftar kategori buku |
| `GET` | `/kategori/create` | `kategori.create` | Formulir tambah kategori |
| `POST` | `/kategori` | `kategori.store` | Simpan kategori baru |
| `GET` | `/kategori/{kategori}` | `kategori.show` | Detail kategori & daftar bukunya |
| `GET` | `/kategori/{kategori}/edit`| `kategori.edit` | Formulir edit kategori |
| `PUT` | `/kategori/{kategori}` | `kategori.update` | Perbarui data kategori |
| `DELETE` | `/kategori/{kategori}` | `kategori.destroy` | Hapus kategori (cascade delete) |

---

## 5. Fitur Validasi & Keamanan
* Validasi Server-Side: ISBN unik, judul minimal 5 karakter, stok minimal 0, kategori valid.
* Proteksi CSRF di seluruh form (`@csrf`).
* Method Spoofing (`@method('PUT')`, `@method('DELETE')`).
* Proteksi Mass Assignment via `$fillable`.
