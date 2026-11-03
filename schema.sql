CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_petugas VARCHAR(100) NOT NULL,
    role ENUM('admin', 'superadmin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    keterangan VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guest_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(150) NOT NULL,
    instansi_asal VARCHAR(150) NOT NULL,
    nomor_whatsapp VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    tujuan_kunjungan TEXT NOT NULL,
    pihak_ditemui VARCHAR(150) NOT NULL,
    foto_verifikasi VARCHAR(255) NOT NULL,
    status_kunjungan ENUM('menunggu', 'diterima', 'selesai', 'dibatalkan') DEFAULT 'menunggu',
    waktu_kunjungan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_visit_id INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_guest FOREIGN KEY (guest_visit_id) REFERENCES guest_visits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS export_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    periode_awal DATE NOT NULL,
    periode_akhir DATE NOT NULL,
    total_data INT DEFAULT 0,
    waktu_unduh TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_export_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, password, nama_petugas, role) VALUES 
('admin', 'admin1123', 'Administrator Inspektorat', 'admin');

INSERT INTO app_settings (setting_key, setting_value, keterangan) VALUES 
('app_name', 'Buku Tamu Online Inspektorat Kota Tangerang', 'Nama Resmi Aplikasi'),
('contact_whatsapp_bantuan', '088976820509', 'Nomor Kontak Bantuan WhatsApp Resmi'),
('instansi', 'Inspektorat Kota Tangerang', 'Nama Instansi Pemilik'),
('notifikasi_admin_otomatis', 'enabled', 'Status Pemicu Notifikasi Admin Otomatis');

INSERT INTO guest_visits (nama_lengkap, instansi_asal, nomor_whatsapp, email, tujuan_kunjungan, pihak_ditemui, foto_verifikasi, status_kunjungan) VALUES 
('Ahmad Fauzi', 'Badan Pengelola Keuangan Daerah', '081234567890', 'fauzi.bpkd@tangerangkota.go.id', 'Koordinasi Laporan Hasil Pemeriksaan Semester I', 'Irban Wilayah I', 'uploads/verifikasi/sample_foto_1.jpg', 'diterima');

INSERT INTO admin_notifications (guest_visit_id, judul, pesan, is_read) VALUES 
(1, 'Kunjungan Tamu Baru', 'Tamu baru atas nama Ahmad Fauzi dari Badan Pengelola Keuangan Daerah telah mengisi buku tamu.', 0);