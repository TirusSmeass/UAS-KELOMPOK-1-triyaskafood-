<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu!']);
    exit;
}

$order_id = $_GET['order_id'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID tidak valid!']);
    exit;
}

try {
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, m.name 
        FROM order_items oi 
        JOIN menu m ON oi.menu_id = m.id 
        WHERE oi.order_id = ? 
        AND oi.order_id IN (SELECT id FROM orders WHERE user_id = ?)
    ");
    $stmt->execute([$order_id, $user_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $html = '';
    foreach ($order_items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $html .= '
        <div class="order-item">
            <div class="item-name">' . htmlspecialchars($item['name']) . '</div>
            <div class="item-details">
                <span>Rp ' . number_format($item['price'], 0, ',', '.') . ' x ' . $item['quantity'] . '</span>
                <span class="item-total">Rp ' . number_format($item_total, 0, ',', '.') . '</span>
            </div>
        </div>';
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>