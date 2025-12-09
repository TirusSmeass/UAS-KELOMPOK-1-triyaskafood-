<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Koneksi database
$host = 'sql100.infinityfree.com';
$dbname = 'if0_40586811_triyaskafood';
$username = 'if0_40586811';
$password = 'Anarus12';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Ambil pesanan user
$stmt = $pdo->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pesanan Saya</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .order-code { color: #e74c3c; font-weight: bold; }
        .status { padding: 5px 10px; border-radius: 5px; display: inline-block; }
        .pending { background: #fff3cd; color: #856404; }
        .btn { padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pesanan Saya</h1>
            <a href="index.php" class="btn">Kembali ke Menu</a>
        </div>
        
        <?php if (!empty($orders)): ?>
            <?php foreach($orders as $order): ?>
            <div class="card">
                <h3>Order: <span class="order-code">#<?= $order['order_code'] ?></span></h3>
                <p>Tanggal: <?= date('d M Y H:i', strtotime($order['order_date'])) ?></p>
                <p>Total: <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></p>
                <p>Status: <span class="status <?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
                <?php if($order['notes']): ?>
                <p>Catatan: <?= htmlspecialchars($order['notes']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="card">
            <h2>Belum ada pesanan</h2>
            <p>Silakan pesan makanan di halaman utama.</p>
            <a href="index.php#menu" class="btn">Pesan Sekarang</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>