-- Buat database
CREATE DATABASE IF NOT EXISTS pesanmakanan;
USE pesanmakanan;

-- Tabel User
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL
);

-- Tabel Produk
CREATE TABLE produk (
    kode VARCHAR(10) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kategori_id INT NOT NULL,
    harga INT NOT NULL,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
);

-- Tabel Pesanan
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_harga INT NOT NULL,
    status ENUM('pending', 'diproses', 'selesai', 'dibatalkan') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- Tabel Detail Pesanan
CREATE TABLE detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    produk_kode VARCHAR(10) NOT NULL,
    jumlah INT NOT NULL,
    harga INT NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id),
    FOREIGN KEY (produk_kode) REFERENCES produk(kode)
);

-- Insert default user (admin dan pelanggan)
-- Password: "password" di-hash dengan bcrypt
INSERT INTO user (username, password, nama, role) VALUES 
('admin', 'root', 'Administrator', 'admin'),
('pelanggan', 'root', 'Pelanggan Contoh', 'pelanggan');

-- Insert data kategori
INSERT INTO kategori (nama) VALUES 
('Makanan'), 
('Minuman'), 
('Cemilan');

-- Insert data produk
INSERT INTO produk (kode, nama, kategori_id, harga) VALUES
('K-01', 'Sate Ayam', 1, 17000),
('K-02', 'Nasi Goreng Telur', 1, 14000),
('K-03', 'Nasi Rames', 1, 12000),
('K-04', 'Lontong Opor', 1, 10000),
('K-05', 'Mie Goreng', 1, 10000),
('K-06', 'Bakso', 1, 10000),
('M-01', 'Es Teh', 2, 5000),
('M-02', 'Es Jeruk', 2, 7000),
('C-01', 'Pangsit', 3, 5000);
