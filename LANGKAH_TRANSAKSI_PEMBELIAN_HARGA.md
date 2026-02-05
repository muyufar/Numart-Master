# Langkah Transaksi Pembelian – Edit Harga & Update Harga Barang Master

## Ringkasan alur

1. **Saat transaksi pembelian** – user bisa **edit harga beli** per barang di keranjang (input + tombol update).
2. **Saat Simpan Pembelian** – list barang di keranjang disimpan ke tabel `pembelian`, dan **harga barang master** di tabel `barang` (field `barang_harga_beli`) di-update sesuai harga yang dipakai di transaksi.
3. **Pencocokan barang** – update master memakai **barang_id** yang sama dengan yang diinputkan di transaksi (dari keranjang/scan).

---

## 1. Edit harga barang di transaksi (sudah jalan)

- **Lokasi:** `transaksi-pembelian-keranjang.php`
- **Kolom "Harga Beli":** input number (step 0.1) + tombol refresh.
- **Perilaku:**
  - User ubah angka lalu klik tombol update → form submit ke `transaksi-pembelian.php` (action `update_harga`).
  - `updateHargaBeliPembelian()` di `aksi/functions.php` meng-update `keranjang_pembelian.keranjang_harga` untuk `keranjang_id` yang dikirim.
- **Subtotal:** otomatis = QTY × Harga Beli (JavaScript), dan Total bawah = jumlah subtotal.

---

## 2. Data yang dikirim saat Simpan Pembelian

- **Lokasi form:** `transaksi-pembelian-keranjang.php` (form dengan tombol "Simpan Pembelian").
- **Hidden per baris (foreach keranjang):**
  - `barang_ids[]` = **barang_id** (dari `$stk['barang_id']`).
  - `keranjang_qty[]` = qty.
  - `barang_harga_beli[]` = harga beli (dari `$stk['keranjang_harga']`, format 1 desimal).
  - Lainnya: `keranjang_id_kasir[]`, `pembelian_invoice[]`, `pembelian_invoice_parent[]`, `pembelian_date[]`, `pembelian_cabang[]`.
- **Di awal halaman:** keranjang yang masih `keranjang_harga = 0` di-update dari `barang.barang_harga_beli` (UPDATE + JOIN), lalu keranjang di-query lagi agar hidden form dapat nilai harga yang benar.

---

## 3. Saat Simpan Pembelian – update barang master

- **Lokasi:** `aksi/functions.php` → fungsi `updateStockPembelian()`.
- **Urutan:**
  1. Insert `invoice_pembelian` (header).
  2. Untuk setiap baris keranjang:
     - Ambil `barang_id` dari `$data['barang_ids'][$x]`.
     - Ambil `harga_beli` dari `$data['barang_harga_beli'][$x]` (jika 0, ambil dari `barang.barang_harga_beli`).
     - Insert ke `pembelian` (barang_id, qty, harga_beli, dll.).
     - **Update barang master:**
       - `UPDATE barang SET barang_harga_beli = <harga_dari_transaksi> WHERE barang_id = <barang_id>`
       - Pencocokan pakai **barang_id** yang sama dengan yang diinputkan di transaksi (dari keranjang = dari scan/pilih barang).
- **Tipe data:** harga dibulatkan 1 desimal (sesuai decimal 11,1).

---

## 4. Pencocokan barang_id / barang_kode

- **Saat ini:** transaksi dan keranjang memakai **barang_id** (setiap item keranjang punya `barang_id`).
- **Update master:** memakai **barang_id** saja:  
  `WHERE barang_id = $barang_id`  
  sehingga `barang_harga_beli` ter-update untuk barang yang sama dengan yang diinputkan di transaksi.
- **Barang_kode:** tabel `barang` punya kolom `barang_kode`. Jika nanti form mengirim juga `barang_kode` per baris, bisa ditambah kondisi `OR barang_kode = '...'` di WHERE; saat ini yang diinputkan dan dipakai adalah **barang_id**.

---

## 5. File yang terlibat

| File | Peran |
|------|--------|
| `transaksi-pembelian-keranjang.php` | Tampilan keranjang, input edit harga & qty, form Simpan Pembelian (hidden barang_ids[], barang_harga_beli[], dll.). |
| `transaksi-pembelian.php` | Terima POST (update_harga / updateStock), panggil fungsi di `aksi/functions.php`. |
| `aksi/functions.php` | `updateHargaBeliPembelian()` (edit harga di keranjang), `updateStockPembelian()` (simpan pembelian + update barang.barang_harga_beli by barang_id). |
| `invoice-pembelian.php` | Tampil invoice: harga & qty dari tabel `pembelian` (data transaksi). |

---

## 6. Ringkas sekali

- **Edit harga:** di keranjang, input harga + tombol update → simpan ke `keranjang_pembelian.keranjang_harga`.
- **Simpan pembelian:** dari keranjang → insert `pembelian` + **update `barang.barang_harga_beli`** per barang sesuai **barang_id** yang diinputkan di transaksi.
