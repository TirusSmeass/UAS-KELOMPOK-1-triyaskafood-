<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = json_decode($_POST['cart_items'], true);
$notes = $_POST['notes'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'transfer';

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart empty']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Create order
    $order_number = 'ORD' . date('YmdHis') . rand(100, 999);
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, notes, payment_method, order_number, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$user_id, $total, $notes, $payment_method, $order_number]);
    $order_id = $pdo->lastInsertId();

    // Insert order items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart_items as $item) {
        $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
    }

    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order created successfully',
        'order_id' => $order_id,
        'order_number' => $order_number
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
}
?>