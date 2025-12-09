<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi database
$host = 'sql100.infinityfree.com';
$dbname = 'if0_40586811_triyaskafood';
$username = 'if0_40586811';
$password = 'Anarus12';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Cek login
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']));
}

// Ambil data dari POST
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['items'])) {
    try {
        $pdo->beginTransaction();
        
        // Generate order code
        $order_code = 'TRF' . date('YmdHis') . mt_rand(100, 999);
        $user_id = $_SESSION['user_id'];
        $notes = isset($data['notes']) ? $data['notes'] : '';
        
        // Data pembayaran dari form
        $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
        $payment_proof = isset($data['payment_proof']) ? $data['payment_proof'] : null;
        $transaction_id = isset($data['transaction_id']) ? $data['transaction_id'] : null;
        $payment_notes = isset($data['payment_notes']) ? $data['payment_notes'] : '';
        
        // Hitung total
        $total_amount = 0;
        foreach ($data['items'] as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }
        
        // 1. Simpan ke tabel ORDERS (TANPA data pembayaran)
        $stmt = $pdo->prepare("INSERT INTO orders (order_code, user_id, total_amount, status, order_date, notes) 
                                VALUES (?, ?, ?, 'pending', NOW(), ?)");
        $stmt->execute([$order_code, $user_id, $total_amount, $notes]);
        $order_id = $pdo->lastInsertId();
        
        // 2. Simpan ke tabel PAYMENTS (data pembayaran terpisah)
        $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, amount, status, payment_date, transaction_id, notes) 
                                VALUES (?, ?, ?, 'pending', NOW(), ?, ?)");
        $stmt->execute([$order_id, $payment_method, $total_amount, $transaction_id, $payment_notes]);
        $payment_id = $pdo->lastInsertId();
        
        // 3. Jika ada bukti pembayaran, update
        if ($payment_proof) {
            $stmt = $pdo->prepare("UPDATE payments SET notes = CONCAT(notes, ' | PROOF: ', ?) WHERE id = ?");
            $stmt->execute([$payment_proof, $payment_id]);
        }
        
        // 4. Coba simpan ke order_items jika tabel ada
        try {
            // Cek apakah tabel order_items ada
            $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, quantity, price) 
                                        VALUES (?, ?, ?)");
                
                foreach ($data['items'] as $item) {
                    $stmt->execute([
                        $order_id,
                        $item['quantity'],
                        $item['price']
                    ]);
                }
            }
        } catch (Exception $e) {
            // Skip jika error
            error_log("Order items skipped: " . $e->getMessage());
        }
        
        // 5. Simpan items sebagai JSON di notes tambahan
        $items_json = json_encode($data['items']);
        $stmt = $pdo->prepare("UPDATE orders SET notes = CONCAT(COALESCE(notes, ''), ' || ITEMS_JSON: ', ?) WHERE id = ?");
        $stmt->execute([$items_json, $order_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat! Status: Menunggu Pembayaran',
            'order_code' => $order_code,
            'order_id' => $order_id,
            'payment_id' => $payment_id,
            'total' => $total_amount
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false, 
            'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
}
?>