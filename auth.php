<?php
// auth.php - VERSI FIXED JSON RESPONSE
session_start();
header('Content-Type: application/json');

require_once 'config.php';

$response = [
    'success' => false,
    'message' => 'Unknown error',
    'redirect' => null
];

try {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'register':
            handleRegister($pdo, $response);
            break;
            
        case 'login':
            handleLogin($pdo, $response);
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    error_log("Auth Error: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
exit;

// ==================== FUNCTIONS ====================

function handleRegister($pdo, &$response) {
    // Validate required fields
    $required = ['full_name', 'username', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $response['message'] = 'Semua field wajib diisi';
            return;
        }
    }
    
    // Clean data
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'];
    
    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Format email tidak valid';
        return;
    }
    
    if (strlen($password) < 6) {
        $response['message'] = 'Password minimal 6 karakter';
        return;
    }
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        $response['message'] = 'Username atau email sudah terdaftar';
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone) 
                          VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$username, $hashed_password, $email, $full_name, $phone])) {
        $response['success'] = true;
        $response['message'] = 'Pendaftaran berhasil! Silakan login.';
        
        // Auto login after registration (optional)
        $user_id = $pdo->lastInsertId();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        $response['redirect'] = 'index.php';
    } else {
        $response['message'] = 'Gagal membuat akun';
    }
}

function handleLogin($pdo, &$response) {
    // Validate
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $response['message'] = 'Username dan password wajib diisi';
        return;
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Find user by username or email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $response['message'] = 'Username atau password salah';
        return;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        $response['message'] = 'Username atau password salah';
        return;
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'customer';
    
    $response['success'] = true;
    $response['message'] = 'Login berhasil!';
    $response['redirect'] = 'index.php';
}
?>