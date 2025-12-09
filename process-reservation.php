<?php
// process-reservation.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu!';
    header('Location: index.php#reservation');
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$reservation_date = $_POST['reservation_date'] ?? '';
$reservation_time = $_POST['reservation_time'] ?? '';
$guests = $_POST['guests'] ?? 1;
$special_requests = $_POST['special_requests'] ?? '';

try {
    $stmt = $pdo->prepare("
        INSERT INTO reservations (user_id, reservation_date, reservation_time, party_size, special_requests)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $reservation_date, $reservation_time, $guests, $special_requests]);
    
    $_SESSION['success'] = 'Reservasi berhasil dibuat!';
    header('Location: dashboard.php');
    exit;
    
} catch(PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
    header('Location: index.php#reservation');
    exit;
}
?>