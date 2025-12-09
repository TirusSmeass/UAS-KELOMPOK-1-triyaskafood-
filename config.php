<?php
// config.php - MySQL VERSION
session_start();

// Database configuration
$host = 'sql100.infinityfree.com';
$dbname = 'if0_40586811_triyaskafood';
$username = 'if0_40586811';
$password = 'Anarus12'; // Ganti dengan password yang benar!

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Buat tabel jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        role ENUM('admin', 'customer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(50),
        image_url VARCHAR(255),
        is_available BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
    
    // Cek dan tambah admin
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $row = $stmt->fetch();
    if ($row['count'] == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) 
                              VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['admin', $hash, 'admin@triyaskafood.com', 'Administrator']);
    }
    
} catch(PDOException $e) {
    // Jangan echo error di sini untuk hindari JSON error
    error_log("Database Error: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}
?>