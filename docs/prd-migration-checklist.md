# PRD & Task Checklist — Migrasi Database Sistem Inventaris

## 1. PRD (Product Requirement Document Singkat)

**Tujuan:** Menerapkan skema database yang telah disepakati ke project Laravel 12 (`telkomsel_inventory`) menggunakan `php artisan make:migration`, dengan urutan yang konsisten agar tidak terjadi error foreign key saat `php artisan migrate` dijalankan.

**Kondisi awal:**
- `0001_01_01_000000_create_users_table.php` — sudah ada (default), **berisi tabel `users`, `password_reset_tokens`, `sessions`**
- `0001_01_01_000001_create_cache_table.php` — sudah ada (default), tidak perlu diubah
- `0001_01_01_000002_create_jobs_table.php` — sudah ada (default), tidak perlu diubah

**Keputusan penting:** `create_users_table.php` **tidak diedit langsung**. Kolom tambahan (`role_id`, `deleted_at`) dibuat lewat migration terpisah yang berjalan setelah tabel `roles` ada — supaya foreign key valid dan urutan migration tetap otomatis benar tanpa perlu mengubah timestamp file manual.

## 2. Guidelines (wajib diikuti agar konsisten)

1. **Jalankan task di bawah satu per satu, sesuai urutan nomor.** Jangan generate semua migration sekaligus lalu diisi belakangan — Laravel menentukan urutan eksekusi dari timestamp nama file, dan `php artisan make:migration` otomatis memberi timestamp *saat command dijalankan*. Menjalankan berurutan = urutan dependency otomatis benar.
2. **Gunakan flag `--create=nama_tabel`** saat membuat tabel baru (Laravel akan auto-generate boilerplate `Schema::create`), dan **`--table=nama_tabel`** saat mengubah tabel yang sudah ada (auto-generate boilerplate `Schema::table`).
3. **Foreign key selalu pakai `$table->foreignId('xxx_id')->constrained('tabel')`**, bukan `unsignedBigInteger` + `foreign()` manual — lebih singkat dan konsisten.
4. **Soft delete wajib** di tabel: `users`, `categories`, `products`, `product_units`.
5. **Setelah tiap migration dibuat dan diisi, langsung tes:**
   ```bash
   php artisan migrate
   ```
   Kalau error, perbaiki dulu sebelum lanjut ke task berikutnya — jangan menumpuk beberapa migration baru sebelum yang sebelumnya berhasil jalan.
6. **Commit ke git setelah tiap migration berhasil di-migrate**, bukan ditumpuk di akhir semua task selesai.
7. **Jangan generate migration untuk `personal_access_tokens` dan `notifications` secara manual** — itu ditangani terpisah lewat `php artisan install:api` dan `php artisan notifications:table` (dibahas di Task 12 & 13).

## 3. Task Checklist

### Task 1 — Tabel `roles`
```bash
php artisan make:migration create_roles_table --create=roles
```
Isi method `up()` pada file yang baru dibuat:
```php
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name', 50)->unique()->comment('admin, staff, manager, karyawan');
    $table->timestamps();
});
```
Test: `php artisan migrate` → pastikan tabel `roles` muncul di database.

---

### Task 2 — Tambah `role_id` & soft delete ke `users` (TANPA mengedit file asli)
```bash
php artisan make:migration add_role_id_and_soft_deletes_to_users_table --table=users
```
Isi method `up()` dan `down()`:
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('role_id')->after('id')->constrained('roles');
        $table->softDeletes();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['role_id']);
        $table->dropColumn('role_id');
        $table->dropSoftDeletes();
    });
}
```
Test: `php artisan migrate` → cek kolom `role_id` dan `deleted_at` sudah muncul di tabel `users`.

---

### Task 3 — Tabel `categories`
```bash
php artisan make:migration create_categories_table --create=categories
```
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('nama_kategori', 150);
    $table->text('deskripsi')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

---

### Task 4 — Tabel `products`
```bash
php artisan make:migration create_products_table --create=products
```
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories');
    $table->string('kode_produk', 50)->unique();
    $table->string('nama_barang', 150);
    $table->text('deskripsi')->nullable();
    $table->string('foto')->nullable();
    $table->unsignedInteger('stok_minimum')->default(1)
        ->comment('ambang notifikasi stok menipis');
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
    $table->softDeletes();

    $table->index('nama_barang');
});
```

---

### Task 5 — Tabel `product_units`
```bash
php artisan make:migration create_product_units_table --create=product_units
```
```php
Schema::create('product_units', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products');
    $table->string('kode_unit', 50)->unique();
    $table->string('qr_code')->unique()->comment('token/path QR untuk stiker');
    $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
    $table->enum('status', [
        'tersedia', 'dipinjam', 'maintenance', 'dilaporkan_hilang', 'hilang_permanen',
    ])->default('tersedia');
    $table->string('lokasi_penyimpanan', 150);
    $table->year('tahun_pengadaan');
    $table->decimal('harga_perolehan', 15, 2)->comment('untuk valuasi laporan tahunan');
    $table->text('catatan')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index('status');
});
```

---

### Task 6 — Tabel `borrowings`
```bash
php artisan make:migration create_borrowings_table --create=borrowings
```
```php
Schema::create('borrowings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->comment('peminjam');
    $table->dateTime('tanggal_pengajuan');
    $table->date('tanggal_pinjam_rencana');
    $table->date('tanggal_kembali_rencana');
    $table->enum('status', [
        'diajukan', 'disetujui', 'ditolak', 'berjalan', 'selesai',
        'dibatalkan_user', 'dibatalkan_otomatis',
    ])->default('diajukan');
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('approved_at')->nullable();
    $table->boolean('fifo_override')->default(false);
    $table->text('alasan_override')->nullable();
    $table->text('alasan_penolakan')->nullable();
    $table->text('catatan')->nullable();
    $table->timestamps();

    $table->index('status');
    $table->index(['tanggal_pinjam_rencana', 'tanggal_kembali_rencana']);
});
```

---

### Task 7 — Tabel `borrowing_details`
```bash
php artisan make:migration create_borrowing_details_table --create=borrowing_details
```
```php
Schema::create('borrowing_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('borrowing_id')->constrained('borrowings');
    $table->foreignId('product_id')->constrained('products')->comment('produk yang diminta');
    $table->foreignId('product_unit_id')->nullable()->constrained('product_units')->nullOnDelete()
        ->comment('terisi saat approval (unit assignment)');
    $table->enum('status', [
        'diajukan', 'disetujui', 'ditolak', 'dipinjam', 'dikembalikan',
        'terlambat', 'bermasalah', 'selesai_bermasalah',
    ])->default('diajukan');
    $table->date('tanggal_kembali_rencana')->comment('bisa override individual saat extend');
    $table->dateTime('tanggal_pinjam_aktual')->nullable()->comment('diisi saat scan QR serah terima');
    $table->dateTime('tanggal_kembali_aktual')->nullable();
    $table->enum('kondisi_saat_pinjam', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
    $table->enum('kondisi_saat_kembali', ['baik', 'rusak_ringan', 'rusak_berat'])->nullable();
    $table->timestamps();

    $table->index('status');
    $table->index('tanggal_kembali_rencana');
});
```

---

### Task 8 — Tabel `borrowing_returns`
```bash
php artisan make:migration create_borrowing_returns_table --create=borrowing_returns
```
```php
Schema::create('borrowing_returns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('borrowing_detail_id')->constrained('borrowing_details');
    $table->dateTime('tanggal_pengembalian');
    $table->foreignId('diterima_oleh')->constrained('users')->comment('Staff penerima');
    $table->enum('kondisi_barang', ['baik', 'rusak_ringan', 'rusak_berat']);
    $table->text('catatan')->nullable();
    $table->timestamps();
});
```

---

### Task 9 — Tabel `incident_reports`
```bash
php artisan make:migration create_incident_reports_table --create=incident_reports
```
```php
Schema::create('incident_reports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('borrowing_detail_id')->constrained('borrowing_details');
    $table->foreignId('product_unit_id')->constrained('product_units');
    $table->foreignId('reported_by')->constrained('users')->comment('karyawan pelapor');
    $table->enum('jenis', ['rusak_ringan', 'rusak_berat', 'hilang']);
    $table->text('kronologi');
    $table->string('foto_bukti')->nullable();
    $table->enum('status', [
        'menunggu_verifikasi_staff', 'terverifikasi_staff', 'menunggu_finalisasi_admin',
        'dibatalkan_ditemukan', 'difinalisasi_admin',
    ])->default('menunggu_verifikasi_staff');
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('verified_at')->nullable();
    $table->date('batas_investigasi')->nullable()->comment('khusus jenis=hilang');
    $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete()
        ->comment('Admin only');
    $table->dateTime('finalized_at')->nullable();
    $table->enum('status_ganti_rugi', ['belum_diselesaikan', 'proses_ganti_rugi', 'selesai'])
        ->nullable();
    $table->text('catatan')->nullable();
    $table->timestamps();

    $table->index('status');
});
```

---

### Task 10 — Tabel `holidays`
```bash
php artisan make:migration create_holidays_table --create=holidays
```
```php
Schema::create('holidays', function (Blueprint $table) {
    $table->id();
    $table->date('tanggal')->unique();
    $table->string('keterangan');
    $table->string('jenis', 30)->comment('libur_nasional / cuti_bersama');
    $table->string('sumber', 20)->default('api')->comment('api / manual');
    $table->timestamps();
});
```

---

### Task 11 — Tabel `report_archives`
```bash
php artisan make:migration create_report_archives_table --create=report_archives
```
```php
Schema::create('report_archives', function (Blueprint $table) {
    $table->id();
    $table->enum('jenis', ['bulanan', 'kuartalan', 'tahunan', 'custom']);
    $table->date('periode_mulai');
    $table->date('periode_selesai');
    $table->decimal('total_nilai_aset', 18, 2)->nullable();
    $table->decimal('total_kerugian', 18, 2)->nullable();
    $table->string('file_pdf_path')->nullable();
    $table->string('file_excel_path')->nullable();
    $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete()
        ->comment('null jika auto-generate terjadwal');
    $table->dateTime('generated_at');
    $table->timestamps();
});
```

---

### Task 12 — Tabel `personal_access_tokens` (Sanctum)
```bash
php artisan install:api
php artisan migrate
```
Command ini generate migration resmi dari package Sanctum secara otomatis — jangan dibuat manual.

---

### Task 13 — Tabel `notifications`
```bash
php artisan notifications:table
php artisan migrate
```

---

### Task 14 — Seeder role wajib
```bash
php artisan make:seeder RoleSeeder
```
Isi `run()`:
```php
public function run(): void
{
    DB::table('roles')->insert([
        ['name' => 'admin',    'created_at' => now(), 'updated_at' => now()],
        ['name' => 'staff',    'created_at' => now(), 'updated_at' => now()],
        ['name' => 'manager',  'created_at' => now(), 'updated_at' => now()],
        ['name' => 'karyawan', 'created_at' => now(), 'updated_at' => now()],
    ]);
}
```
Daftarkan di `DatabaseSeeder.php`, lalu jalankan:
```bash
php artisan db:seed --class=RoleSeeder
```

## 4. Verifikasi Akhir
```bash
php artisan migrate:status
```
Pastikan semua migration berstatus `Ran`, lalu cek struktur tabel langsung di MySQL (`DESCRIBE nama_tabel;`) untuk memastikan sesuai skema ERD yang disepakati.
