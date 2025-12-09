<?php
// setup-database.php - File untuk setup database otomatis

// Koneksi tanpa memilih database dulu
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS triyaska_food CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE triyaska_food");

    // Buat tabel users
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Buat tabel products
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT PRIMARY KEY AUTO_INCREMENT,
            product_code VARCHAR(20) UNIQUE NOT NULL,
            product_name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            description TEXT,
            is_available BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Buat tabel orders
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NULL,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            payment_method VARCHAR(50) DEFAULT 'cash',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Buat tabel order_items
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id INT NOT NULL,
            product_id INT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )
    ");

    // Insert sample user
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("
        INSERT IGNORE INTO users (username, password, name, email) 
        VALUES ('admin', '$hashed_password', 'Administrator', 'admin@triyaskafood.com')
    ");

    // Insert sample products
    $pdo->exec("
        INSERT IGNORE INTO products (product_code, product_name, price, description) VALUES
        ('E5', 'E5 Teh Lemon', 20000, 'Es Teh Lemon Segar'),
        ('A1', 'Kopi Hitam', 15000, 'Kopi Hitam Original'),
        ('B2', 'Nasi Goreng', 25000, 'Nasi Goreng Spesial'),
        ('C3', 'Mie Ayam', 18000, 'Mie Ayam Bakso'),
        ('D4', 'Gado-gado', 22000, 'Gado-gado Lengkap')
    ");

    echo "✅ Database setup berhasil!<br>";
    echo "✅ Tabel-tabel berhasil dibuat<br>";
    echo "✅ User sample: admin / admin123<br>";
    echo "✅ <a href='login.php'>Login sekarang</a>";

} catch (PDOException $e) {
    die("Setup database gagal: " . $e->getMessage());
}
?>