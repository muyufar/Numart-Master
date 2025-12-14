-- =====================================================
-- SQL Update untuk Fitur Customer Management
-- Jalankan query ini di phpMyAdmin atau MySQL console
-- =====================================================

-- 1. Tambahkan kolom alamat detail ke tabel customer
ALTER TABLE `customer` 
ADD COLUMN `alamat_dusun` VARCHAR(100) NULL DEFAULT NULL AFTER `customer_alamat`,
ADD COLUMN `alamat_desa` VARCHAR(100) NULL DEFAULT NULL AFTER `alamat_dusun`,
ADD COLUMN `alamat_kecamatan` VARCHAR(100) NULL DEFAULT NULL AFTER `alamat_desa`,
ADD COLUMN `alamat_kabupaten` VARCHAR(100) NULL DEFAULT NULL AFTER `alamat_kecamatan`,
ADD COLUMN `alamat_provinsi` VARCHAR(100) NULL DEFAULT NULL AFTER `alamat_kabupaten`,
ADD COLUMN `alamat_kode_provinsi` VARCHAR(10) NULL DEFAULT NULL AFTER `alamat_provinsi`,
ADD COLUMN `alamat_kode_kabupaten` VARCHAR(10) NULL DEFAULT NULL AFTER `alamat_kode_provinsi`,
ADD COLUMN `alamat_kode_kecamatan` VARCHAR(15) NULL DEFAULT NULL AFTER `alamat_kode_kabupaten`,
ADD COLUMN `alamat_kode_desa` VARCHAR(20) NULL DEFAULT NULL AFTER `alamat_kode_kecamatan`;

-- 2. Buat tabel untuk setting target belanja customer
CREATE TABLE IF NOT EXISTS `customer_target_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cabang` INT NOT NULL DEFAULT 0,
    `target_harian` DECIMAL(15,2) DEFAULT 0,
    `target_mingguan` DECIMAL(15,2) DEFAULT 0,
    `target_bulanan` DECIMAL(15,2) DEFAULT 100000,
    `target_tahunan` DECIMAL(15,2) DEFAULT 1200000,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_cabang` (`cabang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `customer_target_settings` (`cabang`, `target_bulanan`, `target_tahunan`) 
VALUES (0, 100000, 1200000)
ON DUPLICATE KEY UPDATE `target_bulanan` = VALUES(`target_bulanan`);

-- 3. Buat tabel untuk riwayat WA Blast
CREATE TABLE IF NOT EXISTS `wa_blast_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cabang` INT NOT NULL DEFAULT 0,
    `user_id` INT NOT NULL,
    `message_template` TEXT NOT NULL,
    `total_recipients` INT DEFAULT 0,
    `blast_type` VARCHAR(50) DEFAULT 'manual',
    `filter_criteria` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Buat tabel untuk tracking pengiriman WA
CREATE TABLE IF NOT EXISTS `wa_blast_recipients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `blast_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    `sent_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_blast_id` (`blast_id`),
    INDEX `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Buat tabel untuk template WA
CREATE TABLE IF NOT EXISTS `wa_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cabang` INT NOT NULL DEFAULT 0,
    `template_name` VARCHAR(100) NOT NULL,
    `template_content` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample templates
INSERT INTO `wa_templates` (`cabang`, `template_name`, `template_content`) VALUES
(0, 'Promosi Umum', 'Halo {nama_customer}! 🛒\n\nKami memiliki promo menarik untuk Anda! Kunjungi toko kami untuk mendapatkan diskon spesial.\n\nSalam,\nNumart'),
(0, 'Reminder Belanja', 'Halo {nama_customer}! 👋\n\nSudah lama tidak berbelanja di toko kami. Kami kangen dengan Anda! 😊\n\nKunjungi kami untuk melihat produk-produk terbaru.\n\nSalam hangat,\nNumart'),
(0, 'Ucapan Terima Kasih', 'Halo {nama_customer}! 🙏\n\nTerima kasih telah menjadi pelanggan setia kami. Total belanja Anda bulan ini: Rp {total_belanja}\n\nKami sangat menghargai kepercayaan Anda!\n\nSalam,\nNumart'),
(0, 'Info Stok Baru', 'Halo {nama_customer}! 🆕\n\nKami baru saja mendapatkan stok produk baru yang mungkin Anda sukai!\n\nKunjungi toko kami segera sebelum kehabisan.\n\nSalam,\nNumart');

-- 6. Buat tabel untuk customer tags/labels (fitur rekomendasi)
CREATE TABLE IF NOT EXISTS `customer_tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cabang` INT NOT NULL DEFAULT 0,
    `tag_name` VARCHAR(50) NOT NULL,
    `tag_color` VARCHAR(7) DEFAULT '#007bff',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_tag` (`cabang`, `tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default tags
INSERT INTO `customer_tags` (`cabang`, `tag_name`, `tag_color`) VALUES
(0, 'VIP', '#ffc107'),
(0, 'Loyal', '#28a745'),
(0, 'Baru', '#17a2b8'),
(0, 'Pasif', '#dc3545'),
(0, 'Potensial', '#6f42c1');

-- 7. Buat tabel relasi customer dengan tags
CREATE TABLE IF NOT EXISTS `customer_tag_relations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_relation` (`customer_id`, `tag_id`),
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_tag` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Buat tabel untuk catatan customer (fitur rekomendasi)
CREATE TABLE IF NOT EXISTS `customer_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `note_content` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tambahkan kolom tanggal lahir untuk customer (fitur rekomendasi - birthday greeting)
ALTER TABLE `customer` 
ADD COLUMN `customer_birthday` DATE NULL DEFAULT NULL AFTER `customer_email`;

