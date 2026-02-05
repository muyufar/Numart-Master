# Rangkuman File yang Diganti (Laporan Laba Bersih Accrual)

## File yang diubah

### 1. `laba-bersih-laporan-accural.php`

Satu-satunya file yang dimodifikasi. Perubahan terbagi dua: **logika data** dan **tampilan**.

---

#### A. Perubahan logika / data (perhitungan persediaan awal)

- **Fungsi baru:** `hitungPersediaanAwalDariPembelian($conn, $cabang, $tanggal_awal, $tanggal_akhir)`
  - Menghitung total persediaan barang awal dari tabel `pembelian` (bukan `invoice_pembelian`).
  - Per `barang_id`: nilai transaksi = `barang_qty * barang_harga_beli`, lalu rata-rata per barang = total_nilai / jumlah_transaksi; total persediaan awal = jumlah semua rata-rata.

- **Penggunaan:** Untuk **cabang 0**, `persediaan_awal` sekarang dihitung dengan:
  ```php
  $persediaan_awal = hitungPersediaanAwalDariPembelian($conn, $cabang, $tanggal_awal, $tanggal_akhir);
  ```
  Label tetap: "Total Pembelian Barang".

---

#### B. Perubahan tampilan (satu tema dengan laporan lain)

- **Content header**
  - Judul: "Laporan Laba Bersih (Accrual Basis)" dengan layout `row` + `col-sm-6`.
  - Breadcrumb: Home → Laporan Laba Bersih Accrual (`float-sm-right`).

- **Card filter**
  - Card-tools: tombol collapse dan remove di header.
  - Setiap field (Tanggal Awal, Tanggal Akhir, Cabang) dibungkus `form-group`; input punya `id` untuk script default tanggal.
  - Tombol "Tampilkan" pakai `type="submit"` dan `btn-block`.

- **Card laporan (kartu biru)**
  - Perbaikan struktur HTML (penghapusan satu `</div>` berlebih).
  - Tombol Export Excel, Export PDF, Print diberi class `no-print`.

- **Card Neraca**
  - Perbaikan letak tag `<small>` periode di dalam div judul.
  - Tombol Excel/PDF diberi class `no-print`.

---

## File lain

Tidak ada file lain yang diubah. Semua perubahan hanya di **`laba-bersih-laporan-accural.php`**.

---

*Dibuat: 31 Januari 2025*
