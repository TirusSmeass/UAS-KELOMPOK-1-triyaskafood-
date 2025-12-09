<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    try {
        // For cash payment, just update order status
        if ($payment_method === 'cash') {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ? AND user_id = ?");
            $stmt->execute([$order_id, $_SESSION['user_id']]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Pesanan dikonfirmasi! Silakan bayar di tempat.'
            ]);
        } 
        // For other payments, create payment record
        else {
            // Get order total
            $stmt = $pdo->prepare("SELECT total_amount FROM orders WHERE id = ? AND user_id = ?");
            $stmt->execute([$order_id, $_SESSION['user_id']]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                throw new Exception('Pesanan tidak ditemukan!');
            }
            
            // Handle file upload for payment proof
            $proof_url = '';
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/payments/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
                $filename = 'payment_' . $order_id . '_' . time() . '.' . $file_extension;
                $proof_url = $upload_dir . $filename;
                
                if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $proof_url)) {
                    throw new Exception('Gagal mengupload bukti pembayaran!');
                }
            }
            
            // Create payment record
            $stmt = $pdo->prepare("
                INSERT INTO payments (order_id, payment_method, amount, proof_url, status) 
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$order_id, $payment_method, $order['total_amount'], $proof_url]);
            
            // Update order status
            $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi.'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>