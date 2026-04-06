-- =============================================================================
-- Seed contoh hierarki COA untuk tabel laba_kategori
-- Jalankan di phpMyAdmin / MySQL SETELAH kolom parent_id & level ada.
--
-- Menyisipkan: Level 1 → Level 2 → Level 3 → Sub (level 4) untuk cabang 0 (PCNU).
-- Sesuaikan @cabang jika akun harus untuk cabang toko tertentu.
-- =============================================================================

SET @cabang := 0;

-- ----- Rantai 1: Aktiva (kas) -----
INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (NULL, 1, 'AKTIVA LANCAR', 'AL', 'DEMO-1-L1', 'aktiva', 'debit', 0.00, @cabang, NOW());
SET @l1_akt := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l1_akt, 2, 'KAS & SETARA KAS', 'KSK', 'DEMO-1-L2', 'aktiva', 'debit', 0.00, @cabang, NOW());
SET @l2_akt := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l2_akt, 3, 'KAS', 'KAS', 'DEMO-1-L3', 'aktiva', 'debit', 0.00, @cabang, NOW());
SET @l3_akt := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l3_akt, 4, 'KAS KECIL OPERASIONAL', 'KKO', 'DEMO-1-SUB', 'aktiva', 'debit', 0.00, @cabang, NOW());

-- ----- Rantai 2: Pendapatan -----
INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (NULL, 1, 'PENDAPATAN USAHA', 'PU', 'DEMO-2-L1', 'pendapatan', 'kredit', 0.00, @cabang, NOW());
SET @l1_pend := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l1_pend, 2, 'PENDAPATAN JASA', 'PJ', 'DEMO-2-L2', 'pendapatan', 'kredit', 0.00, @cabang, NOW());
SET @l2_pend := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l2_pend, 3, 'PENDAPATAN JASA UMUM', 'PJU', 'DEMO-2-L3', 'pendapatan', 'kredit', 0.00, @cabang, NOW());
SET @l3_pend := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l3_pend, 4, 'PENDAPATAN ADMINISTRASI', 'PAD', 'DEMO-2-SUB', 'pendapatan', 'kredit', 0.00, @cabang, NOW());

-- ----- Rantai 3: Beban -----
INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (NULL, 1, 'BEBAN OPERASIONAL', 'BO', 'DEMO-3-L1', 'beban', 'debit', 0.00, @cabang, NOW());
SET @l1_beb := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l1_beb, 2, 'BEBAN UMUM & ADMIN', 'BUA', 'DEMO-3-L2', 'beban', 'debit', 0.00, @cabang, NOW());
SET @l2_beb := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l2_beb, 3, 'BEBAN KANTOR', 'BK', 'DEMO-3-L3', 'beban', 'debit', 0.00, @cabang, NOW());
SET @l3_beb := LAST_INSERT_ID();

INSERT INTO laba_kategori (parent_id, level, name, kategori_name, kode_akun, kategori, tipe_akun, saldo, cabang, create_at)
VALUES (@l3_beb, 4, 'ATK & ALAT TULIS', 'ATK', 'DEMO-3-SUB', 'beban', 'debit', 0.00, @cabang, NOW());
