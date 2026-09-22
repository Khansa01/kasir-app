CREATE DATABASE IF NOT EXISTS kasir_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kasir_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir') DEFAULT 'kasir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT,
    kode VARCHAR(50) UNIQUE,
    nama VARCHAR(150) NOT NULL,
    harga DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    satuan VARCHAR(30) DEFAULT 'pcs',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
);

CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(30) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    bayar DECIMAL(15,2) NOT NULL DEFAULT 0,
    kembalian DECIMAL(15,2) NOT NULL DEFAULT 0,
    metode_bayar ENUM('tunai','transfer','qris') DEFAULT 'tunai',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE transaksi_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    produk_id INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    harga DECIMAL(15,2) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id)
);

INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Kasir Utama', 'kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir');

INSERT INTO kategori (nama) VALUES ('Makanan'), ('Minuman'), ('Snack'), ('Lainnya');

INSERT INTO produk (kategori_id, kode, nama, harga, stok, satuan) VALUES
(1, 'MKN-001', 'Nasi Goreng', 15000, 100, 'porsi'),
(1, 'MKN-002', 'Mie Goreng', 13000, 100, 'porsi'),
(2, 'MNM-001', 'Es Teh Manis', 5000, 200, 'gelas'),
(2, 'MNM-002', 'Es Jeruk', 7000, 150, 'gelas'),
(3, 'SNK-001', 'Keripik Singkong', 8000, 50, 'bungkus');