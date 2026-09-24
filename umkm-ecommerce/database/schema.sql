-- =====================================================================
-- UMKM E-COMMERCE DATABASE SCHEMA
-- Tugas: Rekayasa E-Bisnis - Pengembangan Website E-commerce (Model ERP)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS umkm_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE umkm_ecommerce;

-- ---------------------------------------------------------------------
-- USERS (pelanggan & admin UMKM)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- CATEGORIES
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- PRODUCTS (terhubung ke modul Inventaris)
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    low_stock_threshold INT NOT NULL DEFAULT 5,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- CART (keranjang belanja per user)
-- ---------------------------------------------------------------------
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- ORDERS (terhubung ke modul Logistik & Keuangan)
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cod','transfer') NOT NULL DEFAULT 'cod',
    delivery_method ENUM('delivery','pickup') NOT NULL DEFAULT 'delivery',
    shipping_address TEXT,
    phone VARCHAR(20),
    notes VARCHAR(255),
    status ENUM('pending','confirmed','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- ORDER ITEMS (detail barang per pesanan)
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- STOCK HISTORY (jejak sinkronisasi stok / modul Inventaris)
-- ---------------------------------------------------------------------
CREATE TABLE stock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    change_type ENUM('in','out') NOT NULL,
    quantity INT NOT NULL,
    stock_after INT NOT NULL,
    reference VARCHAR(100) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- NOTIFICATIONS (untuk dashboard admin - notifikasi pesanan masuk)
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('new_order','low_stock','order_status') NOT NULL DEFAULT 'new_order',
    reference_id INT DEFAULT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- FINANCE RECORDS (modul Keuangan - sinkron otomatis dari transaksi)
-- ---------------------------------------------------------------------
CREATE TABLE finance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT DEFAULT NULL,
    type ENUM('income','expense') NOT NULL DEFAULT 'income',
    amount DECIMAL(12,2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;

-- =====================================================================
-- SEED DATA (contoh, silakan sesuaikan dengan UMKM mitra kelompok Anda)
-- =====================================================================

INSERT INTO users (name, email, password, phone, role) VALUES
('Admin UMKM', 'admin@umkm.test', '$2y$10$gtC7jAg7rIu8jTZNJt8zn.NfHRu3EpLrGjcWgyR8uCW29Cng/qBBu', '085881280298', 'admin');
-- Catatan: password di atas hanyalah placeholder. Gunakan halaman /register.php
-- untuk membuat akun admin nyata (password akan di-hash otomatis oleh aplikasi),
-- lalu jalankan: UPDATE users SET role='admin' WHERE email='email_anda@...';

INSERT INTO categories (name) VALUES
('Makanan'),
('Minuman'),
('Lainnya');

INSERT INTO products (category_id, name, description, price, stock, image) VALUES
(1, 'Bebek Goreng', 'Bebek goreng gurih dengan bumbu khas Madura.', 29500, 12, 'bebek_goreng.jpg'),
(1, 'Bebek Bakar', 'Bebek bakar empuk dengan bumbu khas Madura.', 30000, 12, 'bebek_bakar.jpg'),
(1, 'Ayam Goreng', 'Ayam goreng renyah dengan bumbu khas Madura.', 25500, 12, 'ayam_goreng.jpg'),
(1, 'Ayam Bakar', 'Ayam bakar lezat dengan bumbu khas Madura.', 28000, 12, 'ayam_bakar.jpg');
