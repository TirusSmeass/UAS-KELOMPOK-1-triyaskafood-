<?php
// cart.php - FIXED VERSION
ob_start();
require_once 'config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu!'
    ]);
    ob_end_flush();
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    if ($action === 'add_to_cart') {
        $product_id = $_POST['menu_id'] ?? $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        // Check if product exists
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan!']);
            ob_end_flush();
            exit;
        }
        
        // Check if already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $new_qty = $existing['quantity'] + $quantity;
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_qty, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        // Get cart count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_count = $stmt->fetch()['count'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan!',
            'cart_count' => $cart_count
        ]);
        
    } elseif ($action === 'get_cart') {
        $stmt = $pdo->prepare("
            SELECT c.*, p.name, p.price, p.image_url 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();
        
        $total = 0;
        $html = '';
        
        if (empty($cart_items)) {
            $html = '<div class="empty-cart-message">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Keranjang belanja kosong</p>
                    </div>';
        } else {
            foreach ($cart_items as $item) {
                $item_total = $item['price'] * $item['quantity'];
                $total += $item_total;
                
                $html .= '
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="' . ($item['image_url'] ?: 'https://via.placeholder.com/100') . '" alt="' . htmlspecialchars($item['name']) . '">
                    </div>
                    <div class="cart-item-details">
                        <h4>' . htmlspecialchars($item['name']) . '</h4>
                        <p class="item-price">Rp ' . number_format($item['price'], 0, ',', '.') . '</p>
                        <div class="cart-quantity-controls">
                            <button class="cart-qty-btn cart-minus" data-cart-id="' . $item['id'] . '">-</button>
                            <span class="cart-quantity">' . $item['quantity'] . '</span>
                            <button class="cart-qty-btn cart-plus" data-cart-id="' . $item['id'] . '">+</button>
                            <button class="cart-remove-btn cart-remove" data-cart-id="' . $item['id'] . '">Hapus</button>
                        </div>
                    </div>
                    <div class="cart-item-total">
                        <span>Rp ' . number_format($item_total, 0, ',', '.') . '</span>
                    </div>
                </div>';
            }
        }
        
        echo json_encode([
            'success' => true,
            'html' => $html,
            'total' => $total,
            'formatted_total' => 'Rp ' . number_format($total, 0, ',', '.'),
            'count' => count($cart_items)
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Action tidak valid!'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}

ob_end_flush();
// ⚠️ TIDAK PAKAI ?> 