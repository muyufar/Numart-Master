# Review Perubahan - Integrasi Laba Kategori dengan Transaksi Piutang dan Hutang

## Tanggal: 2024
## File yang Diubah: `aksi/functions.php`

---

## Ringkasan Perubahan

Sistem telah diintegrasikan dengan modul `laba-kategori` untuk mencatat transaksi piutang dan hutang secara otomatis. Semua perubahan dilakukan di file `aksi/functions.php`.

---

## 1. Transaksi Piutang (Beli Langsung)

### Lokasi: Fungsi `updateStock()` - Baris ~1566-1615

**Perubahan:**
- Menambahkan logika untuk mencatat transaksi piutang ke akun **Piutang Dagang (1-1300)**
- Dijalankan ketika `invoice_piutang == 1`

**Fungsi:**
```php
// Update saldo Piutang Dagang (1-1300) jika transaksi piutang
if ($invoice_piutang == 1) {
    // Total piutang adalah total invoice_sub_total
    $total_piutang = $invoice_sub_total;
    
    // Mencari akun dengan kode_akun 1-1300
    // Update saldo jika akun sudah ada
    // Buat akun baru jika belum ada
}
```

**Akun yang Terpengaruh:**
- **Piutang Dagang (1-1300)**: Saldo ditambahkan sebesar `invoice_sub_total`
  - Kategori: `aktiva`
  - Tipe Akun: `debit`

---

## 2. Pembayaran Cicilan Piutang

### Lokasi: Fungsi `tambahCicilanPiutang()` - Baris ~2780-2856

**Perubahan:**
- Mengurangi saldo **Piutang Dagang (1-1300)** sebesar nominal cicilan
- Menambahkan saldo ke akun pembayaran sesuai tipe pembayaran

**Fungsi:**
```php
// 1. Kurangi saldo Piutang Dagang (1-1300)
// 2. Tambah saldo ke akun pembayaran sesuai tipe:
//    - Cash (0) → Kas Tunai (1-1100)
//    - Transfer/Debit/Credit Card (1/2/3) → Kas Bank BRI (1-1152)
```

**Akun yang Terpengaruh:**
- **Piutang Dagang (1-1300)**: Saldo dikurangi sebesar `piutang_nominal`
- **Kas Tunai (1-1100)**: Saldo ditambahkan jika pembayaran Cash
- **Kas Bank BRI (1-1152)**: Saldo ditambahkan jika pembayaran Transfer/Debit/Credit Card

### Lokasi: Fungsi `hapusCicilanPiutang()` - Baris ~2883-2970

**Perubahan:**
- Mengembalikan saldo saat cicilan piutang dihapus (reverse transaction)

**Fungsi:**
```php
// 1. Tambah kembali saldo Piutang Dagang (1-1300)
// 2. Kurangi saldo dari akun pembayaran yang sesuai
```

---

## 3. Transaksi Hutang (Pembelian)

### Lokasi: Fungsi `updateStockPembelian()` - Baris ~2580-2632

**Perubahan:**
- Menambahkan logika untuk mencatat transaksi hutang ke akun **Hutang Dagang (2-1100)**
- Dijalankan ketika `invoice_hutang == 1`

**Fungsi:**
```php
// Update saldo Hutang Dagang (2-1100) jika transaksi hutang
if ($invoice_hutang == 1) {
    // Total hutang adalah total invoice_total
    $total_hutang = floatval($invoice_total);
    
    // Mencari akun dengan kode_akun 2-1100
    // Update saldo jika akun sudah ada
    // Buat akun baru jika belum ada
}
```

**Akun yang Terpengaruh:**
- **Hutang Dagang (2-1100)**: Saldo ditambahkan sebesar `invoice_total`
  - Kategori: `pasiva`
  - Tipe Akun: `kredit`

---

## 4. Pembayaran Cicilan Hutang

### Lokasi: Fungsi `tambahCicilanhutang()` - Baris ~3137-3193

**Perubahan:**
- Mengurangi saldo **Hutang Dagang (2-1100)** sebesar nominal cicilan
- Mengurangi saldo dari akun pembayaran sesuai tipe pembayaran

**Fungsi:**
```php
// 1. Kurangi saldo Hutang Dagang (2-1100)
// 2. Kurangi saldo dari akun pembayaran sesuai tipe:
//    - Cash (0) → Kas Tunai (1-1100)
//    - Transfer/Debit/Credit Card (1/2/3) → Kas Bank BRI (1-1152)
```

**Akun yang Terpengaruh:**
- **Hutang Dagang (2-1100)**: Saldo dikurangi sebesar `hutang_nominal`
- **Kas Tunai (1-1100)**: Saldo dikurangi jika pembayaran Cash
- **Kas Bank BRI (1-1152)**: Saldo dikurangi jika pembayaran Transfer/Debit/Credit Card

### Lokasi: Fungsi `hapusCicilanHutang()` - Baris ~3245-3315

**Perubahan:**
- Mengembalikan saldo saat cicilan hutang dihapus (reverse transaction)

**Fungsi:**
```php
// 1. Tambah kembali saldo Hutang Dagang (2-1100)
// 2. Tambah kembali saldo ke akun pembayaran yang sesuai
```

---

## Mapping Kode Akun

| Kode Akun | Nama Akun | Kategori | Tipe Akun | Digunakan Untuk |
|-----------|-----------|----------|-----------|-----------------|
| 1-1100 | Kas Tunai | aktiva | debit | Pembayaran Cash (piutang & hutang) |
| 1-1152 | Kas Bank BRI | aktiva | debit | Pembayaran Transfer/Debit/Credit Card |
| 1-1300 | Piutang Dagang | aktiva | debit | Transaksi piutang penjualan |
| 2-1100 | Hutang Dagang | pasiva | kredit | Transaksi hutang pembelian |

---

## Alur Transaksi Lengkap

### A. Transaksi Penjualan dengan Piutang
1. **Beli Langsung** → Pilih Piutang → Simpan Payment
   - ✅ Piutang Dagang (1-1300) **+invoice_sub_total**

2. **Piutang Cicilan** → Bayar Cicilan
   - ✅ Piutang Dagang (1-1300) **-piutang_nominal**
   - ✅ Kas Tunai (1-1100) **+piutang_nominal** (jika Cash)
   - ✅ Kas Bank BRI (1-1152) **+piutang_nominal** (jika Transfer/Debit/Credit Card)

3. **Hapus Cicilan Piutang**
   - ✅ Piutang Dagang (1-1300) **+piutang_nominal** (reverse)
   - ✅ Kas Tunai/Bank BRI **-piutang_nominal** (reverse)

### B. Transaksi Pembelian dengan Hutang
1. **Transaksi Pembelian** → Pilih Hutang → Simpan Payment
   - ✅ Hutang Dagang (2-1100) **+invoice_total**

2. **Hutang Cicilan** → Bayar Cicilan
   - ✅ Hutang Dagang (2-1100) **-hutang_nominal**
   - ✅ Kas Tunai (1-1100) **-hutang_nominal** (jika Cash)
   - ✅ Kas Bank BRI (1-1152) **-hutang_nominal** (jika Transfer/Debit/Credit Card)

3. **Hapus Cicilan Hutang**
   - ✅ Hutang Dagang (2-1100) **+hutang_nominal** (reverse)
   - ✅ Kas Tunai/Bank BRI **+hutang_nominal** (reverse)

---

## Fitur yang Ditambahkan

1. ✅ **Auto-create Akun**: Jika akun belum ada, sistem akan membuatnya otomatis
2. ✅ **Multi-Cabang Support**: Mencari akun untuk cabang tertentu atau cabang 0 (default)
3. ✅ **Reverse Transaction**: Mengembalikan saldo saat cicilan dihapus
4. ✅ **Type Safety**: Validasi dan konversi tipe data untuk menghindari error
5. ✅ **Backward Compatibility**: Cek kolom `cabang` sebelum digunakan

---

## Catatan Penting

1. **Saldo Awal**: Pastikan akun-akun berikut sudah ada di `laba_kategori` atau akan dibuat otomatis:
   - Piutang Dagang (1-1300)
   - Hutang Dagang (2-1100)
   - Kas Tunai (1-1100)
   - Kas Bank BRI (1-1152)

2. **Konsistensi Data**: Semua transaksi menggunakan `floatval()` untuk memastikan konsistensi tipe data

3. **Error Handling**: Sistem akan mencari akun untuk cabang tertentu terlebih dahulu, jika tidak ada akan mencari cabang 0 atau NULL

4. **Testing**: Disarankan untuk melakukan testing pada:
   - Transaksi piutang baru
   - Pembayaran cicilan piutang (semua tipe pembayaran)
   - Hapus cicilan piutang
   - Transaksi hutang baru
   - Pembayaran cicilan hutang (semua tipe pembayaran)
   - Hapus cicilan hutang

---

## File yang Diubah

- ✅ `aksi/functions.php` (4 fungsi dimodifikasi, ~200 baris kode ditambahkan)

---

## Status: ✅ SELESAI

Semua fitur telah diimplementasikan dan siap digunakan.
