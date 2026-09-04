# AGENT INSTRUCTIONS: ERP SALES MODULE (LARAVEL + VITE + MYSQL)

## 1. PROJECT OVERVIEW
Kamu adalah AI Coding Agent yang bertugas mengembangkan modul Sales untuk sistem ERP. Pembangunan harus mematuhi standar arsitektur Laravel yang ketat, keamanan data, dan integritas transaksional. Jangan menggunakan asumsi di luar dokumen ini tanpa konfirmasi.

## 2. TECH STACK & DEPLOYMENT TARGET
- **Backend:** Laravel 11.x
- **Frontend:** Laravel Blade / Inertia.js (sesuaikan dengan setup Vite yang ada)
- **Database:** MySQL
- **Asset Build:** Vite
- **Storage:** Local storage untuk tahap pengujian saat ini (harus diimplementasikan menggunakan facade `Storage` Laravel agar siap dimigrasikan ke Amazon S3 tanpa merombak logika bisnis).
- **UI/UX:** Gunakan perpaduan konsep *corporate flat UI* untuk tata letak dan struktur data, dengan elemen *liquid glass aesthetics* pada komponen visual pendukung (seperti modal, kartu ringkasan, atau panel notifikasi).

## 3. DATABASE & ERD IMPLEMENTATION
Implementasikan skema database secara presisi berdasarkan ERD berikut. Perhatikan tipe data, index, dan constraint.

### Aturan Skema Database:
1.  **Soft Deletes:** Wajib diterapkan pada model `Customer`.
2.  **Foreign Keys:** Wajib menggunakan *Foreign Key constraints*. Aturan penghapusan harus `RESTRICT` pada relasi transaksional (misal: Customer tidak bisa di-*hard delete* jika memiliki relasi ke `SalesOrder`).
3.  **Tipe Data Moneter:** Gunakan `DECIMAL(15, 2)` untuk semua kolom harga dan total (`price`, `total_amount`, `subtotal`, `amount`). Jangan gunakan `FLOAT` atau `DOUBLE`.
4.  **Enums:** Status (`status` pada quotations, sales_orders, production_requests, invoices) WAJIB diimplementasikan menggunakan PHP Backed Enums (`enum Status: string`).

## 4. BUSINESS LOGIC & STATE MACHINES

### 4.1. Manajemen Transaksi (Wajib)
Setiap operasi yang melibatkan penyimpanan ke lebih dari satu tabel (contoh: menyimpan `sales_orders` dan `sales_order_items` secara bersamaan) WAJIB dibungkus di dalam `DB::transaction()`. Tidak ada toleransi untuk *partial inserts*.

### 4.2. State Machine Transisi Status
Validasi perubahan status harus dilakukan di *Service Layer*, bukan di Controller.
Aturan transisi yang sah:
- **Quotation:** `draft` -> `sent` -> `accepted` | `rejected`
- **Sales Order:** `draft` -> `confirmed` -> `processing` (menunggu produksi) | `ready` (stok ada/produksi selesai) -> `completed`
- **Invoice:** `unpaid` -> `partial` -> `paid`

### 4.3. RBAC (Role-Based Access Control)
- Role **Sales** memiliki akses penuh (CRUD) pada: Customers, Quotations, Sales Orders, Invoices.
- Role **Sales** memiliki akses READ-ONLY pada: Products (khusus melihat `stock_quantity`), Production Requests.
- Buat *Middleware* atau *Policies* Laravel yang secara ketat memblokir akses *write* Sales ke ranah Produksi.

## 5. STANDARDISASI KODE LARAVEL
1.  **Form Requests:** Gunakan Form Request classes untuk seluruh validasi input. Jangan menempatkan logika validasi di Controller.
2.  **Service Pattern:** Ekstrak logika bisnis yang kompleks (seperti kalkulasi total order, transisi status, memicu production request dari sales order) ke dalam *Service Classes*.
3.  **Model Observers:** Gunakan Observers HANYA untuk aksi non-krusial (seperti *logging*). Jangan gunakan Observers untuk mengelola logika transaksional utama untuk menghindari *side-effect* yang sulit dilacak.
4.  **Repositories:** Gunakan *Repository Pattern* jika query ke MySQL menjadi terlalu kompleks, untuk memisahkan logika akses data.

## 6. DEVELOPMENT WORKFLOW
1. Baca dan pahami seluruh struktur.
2. Eksekusi pembuatan *Migrations* dan *Models* beserta relasinya.
3. Buat *Seeder* untuk data master (Products) dengan stok tiruan.
4. Bangun *Service Layer* untuk logika inti.
5. Selesaikan Controller dan integrasi antarmuka pengguna (UI) melalui Vite.

## 7. STANDAR DESAIN UI/UX & DROPDOWN COMBOBOX (MANDATORY)
Semua form input pemilihan di seluruh aplikasi WAJIB menggunakan standar berikut:
1. **Search-as-you-type Liquid Glass Combobox:**
   - Semua input dropdown relasi/data (misal: Pelanggan, Produk, Branch) WAJIB menggunakan pola *Searchable Combobox* interaktif dengan estetika **Liquid Glass Solid** (`apple-glass-panel bg-white/95 backdrop-blur-3xl border border-white/80 shadow-2xl rounded-2xl`).
   - **Tingkat Transparansi Rendah (Solid & Jelas Terbaca):** Box dropdown wajib memiliki opasitas tinggi (minimal `bg-white/95` atau `bg-white/[0.98]`) agar teks atau elemen tabel di baliknya tidak tembus pandang dan tidak mengganggu keterbacaan.
   - **Efek Buram Komponen yang Tertimpa (*Backdrop Blur*):** Area, tabel, atau baris kontainer di bawah menu dropdown yang tertimpa wajib diburamkan secara mendalam (`backdrop-blur-2xl` atau `backdrop-blur-3xl`).
   - **Opsi Registrasi On-the-Fly di Urutan Paling Atas:** Opsi otomatis **`+ Tambah Baru`** WAJIB selalu diposisikan di **URUTAN PERTAMA / PALING ATAS** dari menu dropdown sejak awal (baik saat kolom input masih kosong maupun saat pengguna sedang mengetik), sehingga admin dapat membuat entri baru *on-the-fly* secara instan tanpa meninggalkan halaman (detail lengkap dapat diselesaikan di lain waktu).
2. **Manajemen Stacking Context & Overflow:**
   - Kontainer tabel dan kartu form WAJIB menggunakan `overflow-visible` dan *z-index* bertingkat (`z-50`) agar menu dropdown mengapung (*floating*) di atas baris dan kontainer bawah tanpa terpotong (*no clipping*).
3. **Kepatuhan Tema Colorless & Icon-less:**
   - Seluruh elemen tetap mematuhi tema monokrom korporat (`slate-900`, `white`, `badge-dark`, subtle borders) dan bebas dari ikon dekoratif pada teks konten.
