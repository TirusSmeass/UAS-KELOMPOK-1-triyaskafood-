<?php
// orders.php
session_start();
require_once 'config.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$orders = [];

try {
    // Query yang benar (tanpa oi.menu_name yang tidak ada)
    $sql = "SELECT 
                o.id,
                o.order_number,
                o.total_amount,
                o.status,
                o.notes,
                o.created_at,
                COUNT(oi.id) as item_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error loading orders: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - TriyaskaFood</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            text-decoration: none;
        }
        
        .logo i {
            color: #e74c3c;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            background: #f8f9fa;
            color: #e74c3c;
        }
        
        .nav-links a.active {
            background: #e74c3c;
            color: white;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Main Content */
        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 200px);
        }
        
        .page-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        /* Orders Section */
        .orders-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        /* Orders List */
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .order-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-info h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .order-number {
            font-weight: 600;
            color: #3498db;
        }
        
        .order-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-status {
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        
        .detail-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        .detail-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .total-amount {
            font-size: 1.2rem;
            color: #e74c3c;
        }
        
        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: #3498db;
            border: 1px solid #3498db;
        }
        
        .btn-outline:hover {
            background: #3498db;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .empty-state h3 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-col h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: white;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #34495e;
            color: #bdc3c7;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            
            .order-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-wrap: wrap;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .orders-section {
                padding: 1.5rem;
            }
            
            .order-card {
                padding: 1rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-utensils"></i>
                    TriyaskaFood
                </a>
                
                <ul class="nav-links">
                    <li><a href="index.php">Beranda</a></li>
                    <li><a href="index.php#menu">Menu</a></li>
                    <li><a href="orders.php" class="active">Pesanan Saya</a></li>
                    <li><a href="index.php#reservation">Reservasi</a></li>
                </ul>
                
                <div class="user-menu">
                    <div class="user-avatar">
                        <?= isset($_SESSION['full_name']) ? strtoupper(substr($_SESSION['full_name'], 0, 1)) : 'U' ?>
                    </div>
                    <span><?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'User' ?></span>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-receipt"></i> Riwayat Pesanan</h1>
                <p>Lihat dan kelola semua pesanan Anda di sini</p>
            </div>
            
            <div class="orders-section">
                <h2 class="section-title">Pesanan Terbaru</h2>
                
                <?php if (!empty($orders)): ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h3>Pesanan #<?= htmlspecialchars($order['order_number']) ?></h3>
                                        <p class="order-date">
                                            <i class="far fa-calendar"></i> 
                                            <?= date('d F Y H:i', strtotime($order['created_at'])) ?>
                                        </p>
                                    </div>
                                    <span class="order-status status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </div>
                                
                                <div class="order-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Total Pesanan</span>
                                        <span class="detail-value total-amount">
                                            Rp <?= number_format($order['total_amount'], 0, ',', '.') ?>
                                        </span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Jumlah Item</span>
                                        <span class="detail-value">
                                            <?= $order['item_count'] ?> item
                                        </span>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Catatan</span>
                                        <span class="detail-value">
                                            <?= !empty($order['notes']) ? htmlspecialchars($order['notes']) : 'Tidak ada catatan' ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="order-actions">
                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Detail Pesanan
                                    </a>
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <button class="btn btn-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                                            <i class="fas fa-times"></i> Batalkan
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline" onclick="printOrder(<?= $order['id'] ?>)">
                                        <i class="fas fa-print"></i> Cetak
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Belum ada pesanan</h3>
                        <p>Silakan pesan menu favorit Anda terlebih dahulu.</p>
                        <a href="index.php#menu" class="btn btn-primary">
                            <i class="fas fa-utensils"></i> Pesan Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>TriyaskaFood</h3>
                    <p>Restoran dengan konsep modern yang menyajikan hidangan lezat dengan bahan-bahan terbaik dan pelayanan terbaik.</p>
                </div>
                
                <div class="footer-col">
                    <h3>Link Cepat</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="index.php#menu">Menu</a></li>
                        <li><a href="orders.php">Pesanan Saya</a></li>
                        <li><a href="index.php#reservation">Reservasi</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Kontak Kami</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> Kampus Ketintang Gedung K4, Surabaya</li>
                        <li><i class="fas fa-phone"></i> +62 21 1234 5678</li>
                        <li><i class="fas fa-envelope"></i> info@triyaskafood.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 TriyaskaFood. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    // Fungsi untuk batalkan pesanan
    function cancelOrder(orderId) {
        if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
            fetch('cancel_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ order_id: orderId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pesanan berhasil dibatalkan');
                    location.reload();
                } else {
                    alert('Gagal membatalkan pesanan: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }
    }
    
    // Fungsi untuk print pesanan
    function printOrder(orderId) {
        window.open('print_order.php?id=' + orderId, '_blank');
    }
    
    // Auto refresh setiap 30 detik untuk update status
    setInterval(() => {
        const hasPendingOrders = document.querySelector('.status-pending');
        if (hasPendingOrders) {
            location.reload();
        }
    }, 30000);
    </script>
</body>
</html>