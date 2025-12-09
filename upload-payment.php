<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? 0;

// Validasi order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'waiting_payment'");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order tidak ditemukan atau sudah diproses");
}

// Proses upload bukti pembayaran
$upload_success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        
        // Validasi file
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'payment_' . $order['order_number'] . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/' . $filename;
            
            // Pastikan folder uploads ada
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Update order dengan bukti pembayaran - SESUAIKAN DENGAN DATABASE
                $stmt = $pdo->prepare("UPDATE orders SET payment_proof = ?, status = 'processing' WHERE id = ?");
                $stmt->execute([$filename, $order_id]);
                
                $upload_success = true;
                $message = 'Bukti pembayaran berhasil diupload! Pesanan sedang diproses.';
            } else {
                $message = 'Gagal mengupload file.';
            }
        } else {
            $message = 'File tidak valid. Hanya boleh JPEG, PNG, JPG, PDF (max 5MB).';
        }
    } else {
        $message = 'Silakan pilih file bukti pembayaran.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - TriyaskaFood</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-utensils"></i>
                    TriyaskaFood
                </a>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="upload-payment-container">
            <h1>Upload Bukti Pembayaran</h1>
            
            <?php if ($upload_success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h3><?= $message ?></h3>
                    <p>Order Number: <strong><?= $order['order_number'] ?></strong></p>
                    <p>Total: <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></p>
                    <div class="action-buttons">
                        <a href="dashboard.php" class="btn-primary">Lihat Dashboard</a>
                        <a href="index.php" class="btn-secondary">Pesan Lagi</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="order-summary">
                    <h3>Ringkasan Order</h3>
                    <div class="summary-details">
                        <p><strong>Order Number:</strong> <?= $order['order_number'] ?></p>
                        <p><strong>Total Amount:</strong> Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></p>
                        <p><strong>Payment Method:</strong> <?= ucfirst($order['payment_method']) ?></p>
                        <?php if(!empty($order['notes'])): ?>
                            <p><strong>Catatan:</strong> <?= htmlspecialchars($order['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="upload-form">
                    <h3>Upload Bukti Transfer</h3>
                    
                    <?php if (!empty($message)): ?>
                        <div class="error-message"><?= $message ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="payment_proof">Pilih File Bukti Pembayaran:</label>
                            <input type="file" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>
                            <small>Format: JPG, PNG, PDF (max 5MB)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_notes">Catatan Pembayaran (optional):</label>
                            <textarea id="payment_notes" name="payment_notes" placeholder="Contoh: Transfer BCA, Nama Pengirim: ..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                        </button>
                    </form>
                </div>
                
                <div class="payment-instruction">
                    <h4>Instruksi Pembayaran:</h4>
                    <ol>
                        <li>Transfer ke rekening BCA: 123-456-7890 (TriyaskaFood)</li>
                        <li>Jumlah transfer: <strong>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></strong></li>
                        <li>Upload bukti transfer di form above</li>
                        <li>Tunggu konfirmasi dari admin</li>
                    </ol>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <style>
    .upload-payment-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem 0;
    }

    .success-message {
        text-align: center;
        background: white;
        padding: 3rem 2rem;
        border-radius: 15px;
        box-shadow: var(--shadow);
    }

    .success-message i {
        font-size: 4rem;
        color: var(--success);
        margin-bottom: 1rem;
    }

    .action-buttons {
        margin-top: 2rem;
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .order-summary, .upload-form {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-soft);
    }

    .payment-instruction {
        background: var(--primary-soft);
        padding: 1.5rem;
        border-radius: 10px;
        border-left: 4px solid var(--primary);
    }
    </style>
</body>
</html>