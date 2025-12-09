<?php
// register.php - VERSI FIXED
header('Content-Type: application/json');  // PASTIKAN ini ada di baris pertama

// Start output buffering untuk catch semua output
ob_start();

require_once 'config.php';

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get form data
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $response['errors'][] = 'All fields are required';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors'][] = 'Invalid email format';
    }
    
    if (strlen($password) < 6) {
        $response['errors'][] = 'Password must be at least 6 characters';
    }
    
    // If no errors, proceed
    if (empty($response['errors'])) {
        // Check if username/email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $response['errors'][] = 'Username or email already exists';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone) 
                                  VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $hashed_password, $email, $fullname, $phone])) {
                $response['success'] = true;
                $response['message'] = 'Registration successful!';
                $response['user_id'] = $pdo->lastInsertId();
            } else {
                $response['errors'][] = 'Failed to create account';
            }
        }
    }
    
} catch (Exception $e) {
    $response['errors'][] = 'System error: ' . $e->getMessage();
}

// Clear any unexpected output
ob_end_clean();

// Output JSON
echo json_encode($response);
exit;
?>