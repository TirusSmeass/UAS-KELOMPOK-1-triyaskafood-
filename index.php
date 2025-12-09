<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// KONEKSI DATABASE YANG BENAR
$host = 'sql100.infinityfree.com';
$dbname = 'if0_40586811_triyaskafood';
$username = 'if0_40586811';
$password = 'Anarus12';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // AMBIL DATA MENU DARI DATABASE
    $menu_items = [];
    try {
        $stmt = $pdo->query("SELECT * FROM menu WHERE is_available = TRUE ORDER BY category, name");
        $menu_items = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error loading menu: " . $e->getMessage());
        $menu_items = [];
    }
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $menu_items = [];
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriyaskaFood - Restoran Modern</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ==================== SEMUA CSS ANDA TETAP SAMA ==================== */
        
        /* Improved Navbar Styles */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 0.8rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.6rem;
            font-weight: bold;
            color: #2c3e50;
            text-decoration: none;
            padding: 0.5rem 0;
        }

        .logo i {
            color: #e74c3c;
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2.5rem;
            margin: 0;
            padding: 0;
        }

        .nav-links li a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links li a:hover {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
            transform: translateY(-2px);
        }

        .nav-links li a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: #e74c3c;
            transition: width 0.3s ease;
        }

        .nav-links li a:hover::after {
            width: 70%;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1.8rem;
        }

        .nav-actions .fa-search {
            font-size: 1.3rem;
            color: #666;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .nav-actions .fa-search:hover {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
        }

        .cart-container {
            position: relative;
            cursor: pointer;
            padding: 0.6rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .cart-container:hover {
            background: rgba(231, 76, 60, 0.1);
            transform: translateY(-2px);
        }

        .cart-container i {
            font-size: 1.4rem;
            color: #666;
            transition: color 0.3s ease;
        }

        .cart-container:hover i {
            color: #e74c3c;
        }

        .cart-count {
            position: absolute;
            top: -3px;
            right: -3px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 2px solid white;
        }

        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .btn-auth {
            padding: 0.75rem 1.5rem;
            border: 2px solid;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }

        .btn-login {
            border-color: #3498db;
            color: #3498db;
        }

        .btn-login:hover {
            background: #3498db;
            color: white;
        }

        .btn-register {
            border-color: #e74c3c;
            color: #e74c3c;
        }

        .btn-register:hover {
            background: #e74c3c;
            color: white;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            background: rgba(52, 152, 219, 0.1);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }

        .user-menu:hover {
            background: rgba(52, 152, 219, 0.2);
            transform: translateY(-2px);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            padding: 0.75rem 0;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(5px);
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.85rem 1.5rem;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .user-dropdown a:hover {
            background: #f8f9fa;
            color: #e74c3c;
            padding-left: 1.75rem;
        }

        .user-dropdown a i {
            width: 18px;
            text-align: center;
            font-size: 1.1rem;
        }

        .menu-toggle {
            display: none;
            font-size: 1.6rem;
            cursor: pointer;
            color: #2c3e50;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .menu-toggle:hover {
            background: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        /* Improved Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(44, 62, 80, 0.85) 0%, rgba(52, 152, 219, 0.75) 100%), 
                        url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80') center/cover no-repeat;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 8rem 0 6rem;
            margin-top: 80px;
        }

        .hero-content {
            text-align: center;
            color: white;
            max-width: 850px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .hero-content h1 {
            font-size: 3.8rem;
            font-weight: 800;
            margin-bottom: 2rem;
            line-height: 1.2;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            letter-spacing: -0.5px;
        }

        .hero-content p {
            font-size: 1.4rem;
            margin-bottom: 3rem;
            line-height: 1.7;
            opacity: 0.95;
            max-width: 650px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 300;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }

        .hero-btns {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero .btn {
            padding: 1.2rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.4s ease;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 220px;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        .hero .btn:first-child {
            background: #e74c3c;
            color: white;
            border: 2px solid #e74c3c;
        }

        .hero .btn:first-child:hover {
            background: #c0392b;
            border-color: #c0392b;
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(231, 76, 60, 0.4);
        }

        .hero .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
            backdrop-filter: blur(10px);
        }

        .hero .btn-outline:hover {
            background: white;
            color: #2c3e50;
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(255, 255, 255, 0.3);
        }

        /* Improved Menu Section Styles */
        .menu {
            background: #f8f9fa;
            padding: 6rem 0;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .menu-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .menu-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .menu-item-image {
            height: 220px;
            overflow: hidden;
        }

        .menu-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .menu-item:hover .menu-item-image img {
            transform: scale(1.05);
        }

        .menu-item-content {
            padding: 1.8rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .menu-item-content h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.8rem;
            line-height: 1.3;
        }

        .menu-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex: 1;
            font-size: 0.95rem;
        }

        .menu-item-footer {
            margin-top: auto;
        }

        .price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #e74c3c;
            text-align: center;
            margin-bottom: 1.2rem;
            display: block;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            margin: 1rem 0;
        }

        .qty-decrease,
        .qty-increase {
            width: 40px;
            height: 40px;
            border: 2px solid #e9ecef;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
            color: #2c3e50;
        }

        .qty-decrease:hover,
        .qty-increase:hover {
            background: #3498db;
            border-color: #3498db;
            color: white;
            transform: scale(1.05);
        }

        .qty-input {
            width: 60px;
            height: 40px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .qty-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            width: 100%;
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.3);
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(231, 76, 60, 0.4);
        }

        .add-to-cart-btn:active {
            transform: translateY(-1px);
        }

        .add-to-cart-btn.loading {
            background: #95a5a6;
            cursor: not-allowed;
            transform: none;
        }

        /* Menu Filters */
        .menu-filters {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.8rem 1.5rem;
            border: 2px solid #3498db;
            background: transparent;
            color: #3498db;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.3);
        }

        /* No Menu State */
        .no-menu {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
            grid-column: 1 / -1;
        }

        .no-menu i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1.5rem;
            display: block;
        }

        .no-menu h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .no-menu p {
            font-size: 1.1rem;
            opacity: 0.8;
        }

        /* Cart Item Styles */
        .cart-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px;
            border-bottom: 1px solid #eee;
            background: white;
            border-radius: 10px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .cart-item-image {
            flex-shrink: 0;
        }

        .cart-item-image img {
            width: 65px;
            height: 65px;
            border-radius: 10px;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-details h4 {
            margin: 0 0 6px 0;
            font-size: 16px;
            color: #2c3e50;
            font-weight: 600;
        }

        .item-price {
            color: #666;
            margin: 0 0 12px 0;
            font-size: 14px;
        }

        .cart-quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-qty-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .cart-qty-btn:hover {
            background: #f8f9fa;
            border-color: #3498db;
            transform: scale(1.05);
        }

        .cart-quantity {
            font-weight: bold;
            min-width: 35px;
            text-align: center;
            font-size: 15px;
        }

        .cart-remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            margin-left: 12px;
            transition: all 0.3s ease;
        }

        .cart-remove-btn:hover {
            background: #c0392b;
            transform: scale(1.05);
        }

        .cart-item-total {
            text-align: right;
            min-width: 110px;
        }

        .cart-item-total span {
            display: block;
            font-weight: bold;
            color: #e74c3c;
            font-size: 17px;
            margin-bottom: 6px;
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 100px;
            right: 30px;
            padding: 18px 24px;
            border-radius: 10px;
            color: white;
            z-index: 10000;
            animation: slideInRight 0.4s ease;
            max-width: 450px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
        }

        .notification-success { background: linear-gradient(135deg, #4CAF50, #45a049); }
        .notification-error { background: linear-gradient(135deg, #f44336, #da190b); }
        .notification-warning { background: linear-gradient(135deg, #ff9800, #e68900); }
        .notification-info { background: linear-gradient(135deg, #2196F3, #0b7dda); }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes bounce {
            0%, 20%, 60%, 100% { transform: translateY(0); }
            40% { transform: translateY(-12px); }
            80% { transform: translateY(-6px); }
        }

        .bounce { animation: bounce 0.7s; }

        /* Empty Cart Message */
        .empty-cart-message {
            text-align: center;
            padding: 50px 30px;
            color: #666;
        }

        .empty-cart-message i {
            font-size: 52px;
            color: #ccc;
            margin-bottom: 18px;
            display: block;
        }

        .empty-cart-message p {
            margin: 0;
            font-size: 17px;
            font-weight: 500;
        }

        /* Container spacing */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2.5rem;
        }

        /* Section spacing */
        section {
            padding: 6rem 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.8rem;
            color: #2c3e50;
            margin-bottom: 1.2rem;
            font-weight: 700;
        }

        .section-title p {
            font-size: 1.3rem;
            color: #666;
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        /* Cart Modal Specific */
        .cart-modal {
            max-width: 600px;
        }

        .cart-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #e74c3c;
            text-align: right;
            margin: 20px 0;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        /* Payment Modal Styles */
        .payment-modal {
            max-width: 700px;
        }

        .payment-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }

        .payment-summary h4 {
            margin-bottom: 15px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bank-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #90caf9;
        }

        .bank-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }

        .bank-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2196F3;
        }

        .bank-item strong {
            color: #1976D2;
            font-size: 1.1rem;
            display: block;
            margin-bottom: 8px;
        }

        .bank-item p {
            margin: 5px 0;
            color: #555;
            font-size: 0.9rem;
        }

        .ewallet-info {
            background: linear-gradient(135deg, #f3e5f5, #e1bee7);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #ce93d8;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .alert i {
            font-size: 1.3rem;
            margin-top: 2px;
        }

        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }

        .alert-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .alert-danger {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        #proof-preview {
            text-align: center;
        }

        #proof-preview img {
            max-width: 250px;
            max-height: 250px;
            border-radius: 10px;
            margin-top: 10px;
            border: 3px solid #ddd;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .payment-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
            counter-reset: step;
        }

        .payment-steps:before {
            content: '';
            position: absolute;
            top: 25px;
            left: 50px;
            right: 50px;
            height: 3px;
            background: #e0e0e0;
            z-index: 1;
        }

        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step:before {
            counter-increment: step;
            content: counter(step);
            width: 50px;
            height: 50px;
            background: #e0e0e0;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
            font-size: 1.2rem;
            border: 4px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .step.active:before {
            background: #3498db;
            animation: pulse 2s infinite;
        }

        .step.completed:before {
            background: #2ecc71;
        }

        .step-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
        }

        /* ==================== TESTIMONIAL STYLES ==================== */
        .testimonials {
            background: #f8f9fa;
            padding: 6rem 0;
        }

        .testimonial-container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        .testimonial-slider {
            overflow: hidden;
            border-radius: 15px;
        }

        .testimonial-track {
            display: flex;
            transition: transform 0.5s ease;
        }

        .testimonial-slide {
            min-width: 100%;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .testimonial-content {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            text-align: center;
            position: relative;
        }

        .testimonial-content:before {
            content: '"';
            font-size: 6rem;
            color: #e74c3c;
            position: absolute;
            top: -20px;
            left: 30px;
            opacity: 0.2;
            font-family: serif;
        }

        .rating {
            color: #FFD700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .testimonial-content p {
            font-size: 1.2rem;
            line-height: 1.7;
            color: #555;
            margin-bottom: 2rem;
            font-style: italic;
        }

        .testimonial-content h4 {
            font-size: 1.3rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .slider-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 2rem;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ddd;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slider-dot.active {
            background: #e74c3c;
            transform: scale(1.2);
        }

        .slider-dot:hover {
            background: #3498db;
        }

        /* ==================== RESERVATION STYLES ==================== */
        .reservation {
            background: white;
            padding: 6rem 0;
        }

        .reservation-form {
            max-width: 800px;
            margin: 0 auto;
            background: #f8f9fa;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-block {
            width: 100%;
        }

        /* About Section */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-image img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .feature {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .feature i {
            color: #e74c3c;
            font-size: 1.5rem;
            margin-top: 0.2rem;
        }

        /* Footer */
        footer {
            background: #2c3e50;
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr;
            gap: 3rem;
            padding-bottom: 3rem;
        }

        .footer-col h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links li a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links li a:hover {
            color: #e74c3c;
        }

        .footer-links li i {
            margin-right: 10px;
            width: 20px;
            color: #3498db;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #e74c3c;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #34495e;
            color: #bdc3c7;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 1.5rem;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                border-radius: 0 0 15px 15px;
                gap: 0.8rem;
            }

            .nav-links.active {
                display: flex;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .hero-content h1 {
                font-size: 2.8rem;
            }
            
            .hero-content p {
                font-size: 1.2rem;
            }
            
            .hero-btns {
                flex-direction: column;
                align-items: center;
                gap: 1.2rem;
            }
            
            .hero .btn {
                width: 100%;
                max-width: 300px;
                padding: 1rem 2rem;
            }

            .container {
                padding: 0 1.5rem;
            }

            .navbar {
                padding: 1rem 0;
            }

            .auth-buttons {
                gap: 0.8rem;
            }

            .btn-auth {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .menu-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .menu-item-content {
                padding: 1.5rem;
            }
            
            .menu-filters {
                gap: 0.5rem;
            }
            
            .filter-btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .about-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .testimonial-content {
                padding: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .reservation-form {
                padding: 2rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .bank-details {
                grid-template-columns: 1fr;
            }

            .payment-modal {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2.2rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }

            .nav-actions {
                gap: 1.2rem;
            }

            .user-menu span {
                display: none;
            }

            .menu-item-image {
                height: 180px;
            }
            
            .menu-item-content {
                padding: 1.2rem;
            }
            
            .menu-item-content h3 {
                font-size: 1.2rem;
            }
            
            .price {
                font-size: 1.2rem;
            }
            
            .testimonial-content {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <i class="fas fa-utensils"></i>
                    TriyaskaFood
                </a>
                
                <ul class="nav-links">
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#about">Tentang</a></li>
                    <li><a href="#menu">Menu</a></li>
                    <li><a href="#testimonials">Testimoni</a></li>
                    <li><a href="#reservation">Reservasi</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-actions">
                    <i class="fas fa-search"></i>
                    <div class="cart-container" id="cartIcon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </div>
                    
                    <div class="auth-buttons">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <div class="user-menu">
                                <div class="user-avatar"><?= isset($_SESSION['full_name']) ? strtoupper(substr($_SESSION['full_name'], 0, 1)) : 'U' ?></div>
                                <span><?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'User' ?></span>
                                <div class="user-dropdown">
                                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <button class="btn-auth btn-login" id="login-btn">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                            <button class="btn-auth btn-register" id="register-btn">
                                <i class="fas fa-user-plus"></i> Daftar
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Modal Login -->
    <div class="modal" id="login-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
            <form id="login-form" method="POST" action="auth.php">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <input type="text" class="form-control" name="username" placeholder="Username atau Email" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Register -->
    <div class="modal" id="register-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-user-plus"></i> Daftar</h2>
            <form id="register-form" method="POST" action="auth.php">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <input type="text" class="form-control" name="full_name" placeholder="Nama Lengkap" required>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="tel" class="form-control" name="phone" placeholder="Nomor Telepon" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cart -->
    <div class="modal" id="cart-modal">
        <div class="modal-content cart-modal">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-shopping-cart"></i> Keranjang Belanja</h2>
            
            <div class="cart-items" id="cartItemsContainer">
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Keranjang belanja kosong</p>
                </div>
            </div>
            
            <div class="cart-total" id="cartTotal">Total: Rp 0</div>
            
            <div class="form-group">
                <textarea class="form-control" id="order-notes" placeholder="Catatan untuk pesanan (opsional)" rows="3"></textarea>
            </div>
            
            <button class="btn btn-block" id="checkout-btn">
                <i class="fas fa-credit-card"></i> Lanjut ke Pembayaran
            </button>
        </div>
    </div>

    <!-- Modal Pembayaran -->
    <div class="modal" id="payment-modal">
        <div class="modal-content payment-modal">
            <span class="close-modal">&times;</span>
            <h2><i class="fas fa-credit-card"></i> Pembayaran</h2>
            
            <div id="payment-summary">
                <!-- Ringkasan pesanan akan muncul di sini -->
            </div>
            
            <form id="payment-form">
                <div class="form-group">
                    <label><i class="fas fa-money-check-alt"></i> Metode Pembayaran</label>
                    <select class="form-control" id="payment-method" required>
                        <option value="">Pilih metode pembayaran</option>
                        <option value="cash">💰 Cash (Bayar di Tempat)</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="dana">💜 DANA</option>
                        <option value="gopay">💚 GoPay</option>
                        <option value="ovo">💙 OVO</option>
                        <option value="shopeepay">🧡 ShopeePay</option>
                    </select>
                </div>
                
                <!-- Info Transfer Bank -->
                <div class="form-group bank-info" id="bank-info" style="display: none;">
                    <h4><i class="fas fa-university"></i> Informasi Rekening</h4>
                    <div class="bank-details">
                        <div class="bank-item">
                            <strong>BCA</strong>
                            <p>No: 1234567890</p>
                            <p>a.n: Triyaska Food</p>
                        </div>
                        <div class="bank-item">
                            <strong>BNI</strong>
                            <p>No: 0987654321</p>
                            <p>a.n: Triyaska Food</p>
                        </div>
                        <div class="bank-item">
                            <strong>Mandiri</strong>
                            <p>No: 1122334455</p>
                            <p>a.n: Triyaska Food</p>
                        </div>
                        <div class="bank-item">
                            <strong>BRI</strong>
                            <p>No: 5566778899</p>
                            <p>a.n: Triyaska Food</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Bank Tujuan</label>
                        <select class="form-control" id="bank-select">
                            <option value="">Pilih Bank</option>
                            <option value="bca">BCA</option>
                            <option value="bni">BNI</option>
                            <option value="mandiri">Mandiri</option>
                            <option value="bri">BRI</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Transaksi / No. Referensi</label>
                        <input type="text" class="form-control" id="transaction-id" 
                               placeholder="Contoh: TRX123456789">
                        <small>Masukkan ID transaksi dari bukti transfer</small>
                    </div>
                </div>
                
                <!-- Info E-Wallet -->
                <div class="form-group ewallet-info" id="ewallet-info" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-mobile-alt"></i>
                        <strong>Pembayaran E-Wallet:</strong> 
                        <p>Gunakan aplikasi untuk scan QR code atau transfer ke nomor berikut:</p>
                        <p><strong>DANA/GoPay/OVO:</strong> 0812-3456-7890 (Triyaska Food)</p>
                    </div>
                    
                    <div class="form-group">
                        <label>ID Transaksi E-Wallet</label>
                        <input type="text" class="form-control" id="ewallet-transaction-id" 
                               placeholder="Contoh: DANA-123456">
                    </div>
                </div>
                
                <!-- Upload Bukti Pembayaran -->
                <div class="form-group" id="upload-proof-section">
                    <label><i class="fas fa-file-upload"></i> Bukti Pembayaran</label>
                    <input type="file" class="form-control" id="payment-proof" 
                           accept="image/*,.pdf,.doc,.docx" required>
                    <small>Upload screenshot/slip pembayaran (JPG, PNG, PDF, max 2MB)</small>
                    <div id="proof-preview" style="margin-top: 10px;"></div>
                </div>
                
                <!-- Catatan Tambahan -->
                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Catatan Pembayaran (Opsional)</label>
                    <textarea class="form-control" id="payment-notes" 
                              placeholder="Contoh: Transfer atas nama John Doe, tanggal 15 Jan 2025" 
                              rows="3"></textarea>
                </div>
                
                <!-- Info Cash -->
                <div class="form-group" id="cash-info" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="fas fa-hand-holding-usd"></i>
                        <strong>Bayar di Tempat:</strong>
                        <p>Silakan datang ke restoran kami untuk melakukan pembayaran.</p>
                        <p>Tunjukkan <strong>Kode Order</strong> Anda kepada kasir.</p>
                        <p>📍 Alamat: Kampus Ketintang Gedung K4, Surabaya</p>
                        <p>⏰ Waktu: 10.00 - 22.00 setiap hari</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-block" id="confirm-payment-btn">
                        <i class="fas fa-paper-plane"></i> Kirim Pesanan & Bukti Pembayaran
                    </button>
                    <p style="text-align: center; margin-top: 10px; font-size: 0.9rem; color: #666;">
                        <i class="fas fa-info-circle"></i> Pesanan akan diproses setelah pembayaran dikonfirmasi
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1>Pengalaman Kuliner Tak Terlupakan</h1>
                <p>Nikmati hidangan lezat yang dibuat dengan bahan-bahan terbaik dan resep warisan turun-temurun.</p>
                <div class="hero-btns">
                    <a href="#menu" class="btn">
                        <i class="fas fa-utensils"></i> Lihat Menu
                    </a>
                    <a href="#reservation" class="btn btn-outline">
                        <i class="fas fa-calendar-check"></i> Reservasi Sekarang
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <div class="section-title">
                <h2>Tentang Kami</h2>
                <p>TriyaskaFood telah melayani hidangan istimewa sejak 2005 dengan komitmen terhadap kualitas dan cita rasa autentik.</p>
            </div>
            
            <div class="about-content">
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1974&q=80" alt="Restoran Interior">
                </div>
                
                <div class="about-text">
                    <h3>Cerita Di Balik Kelezatan Kami</h3>
                    <p>Didirikan oleh Chef Juna, TriyaskaFood berawal dari kecintaan terhadap masakan tradisional dengan sentuhan modern. Setiap hidangan dibuat dengan penuh perhatian terhadap detail dan menggunakan bahan-bahan segar pilihan.</p>
                    <p>Kami percaya bahwa makanan bukan hanya tentang mengisi perut, tetapi juga tentang menciptakan kenangan dan pengalaman yang tak terlupakan.</p>
                    
                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-leaf"></i>
                            <div>
                                <h4>Bahan Organik</h4>
                                <p>Menggunakan bahan-bahan organik terbaik dari petani lokal.</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-award"></i>
                            <div>
                                <h4>Chef Berpengalaman</h4>
                                <p>Dipimpin oleh chef dengan pengalaman lebih dari 15 tahun.</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-heart"></i>
                            <div>
                                <h4>Dibuat dengan Cinta</h4>
                                <p>Setiap hidangan dibuat dengan penuh passion dan dedikasi.</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-recycle"></i>
                            <div>
                                <h4>Ramah Lingkungan</h4>
                                <p>Berkomitmen terhadap praktik berkelanjutan dan ramah lingkungan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section -->
    <section class="menu" id="menu">
        <div class="container">
            <div class="section-title">
                <h2>Menu Spesial Kami</h2>
                <p>Jelajahi berbagai pilihan hidangan lezat yang dibuat khusus untuk memanjakan lidah Anda.</p>
            </div>
            
            <div class="menu-filters">
                <button class="filter-btn active" data-filter="all">Semua</button>
                <button class="filter-btn" data-filter="main">Hidangan Utama</button>
                <button class="filter-btn" data-filter="appetizer">Hidangan Pembuka</button>
                <button class="filter-btn" data-filter="dessert">Pencuci Mulut</button>
                <button class="filter-btn" data-filter="drink">Minuman</button>
            </div>
            
            <div class="menu-grid" id="menuGrid">
                <?php if(!empty($menu_items)): ?>
                    <?php foreach($menu_items as $item): ?>
                    <div class="menu-item" data-category="<?= htmlspecialchars($item['category']) ?>">
                        <div class="menu-item-image">
                            <img src="<?= htmlspecialchars($item['image_url'] ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80') ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'">
                        </div>
                        <div class="menu-item-content">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="menu-description"><?= htmlspecialchars($item['description']) ?></p>
                            <div class="menu-item-footer">
                                <span class="price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                
                                <!-- Quantity Selector -->
                                <div class="quantity-selector">
                                    <button type="button" class="qty-decrease" data-menu-id="<?= $item['id'] ?>">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" class="qty-input" 
                                           data-menu-id="<?= $item['id'] ?>" 
                                           value="1" min="1" max="10"
                                           id="qty-<?= $item['id'] ?>">
                                    <button type="button" class="qty-increase" data-menu-id="<?= $item['id'] ?>">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <button class="add-to-cart-btn" 
                                        data-menu-id="<?= $item['id'] ?>" 
                                        data-menu-name="<?= htmlspecialchars($item['name']) ?>"
                                        data-menu-price="<?= $item['price'] ?>">
                                    <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-menu" id="noMenuMessage">
                        <i class="fas fa-utensils"></i>
                        <h3>Menu Sedang Tidak Tersedia</h3>
                        <p>Silakan kembali lagi nanti</p>
                        <button onclick="window.location.href='insert_sample_menu.php'" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-sync"></i> Load Sample Menu
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Apa Kata Pelanggan</h2>
                <p>Pengalaman nyata dari pelanggan yang telah menikmati hidangan kami.</p>
            </div>
            
            <div class="testimonial-container">
                <div class="testimonial-slider">
                    <div class="testimonial-track" id="testimonialTrack">
                        <!-- Testimonial 1 -->
                        <div class="testimonial-slide">
                            <div class="testimonial-content">
                                <div class="rating">
                                    ★★★★★
                                </div>
                                <p>"Makanan di sini luar biasa! Steak nya empuk dan bumbunya pas. Pelayanan juga sangat ramah. Sudah beberapa kali bawa keluarga ke sini dan selalu puas."</p>
                                <h4>Sarah Wijaya</h4>
                                <small>Pelanggan Setia</small>
                            </div>
                        </div>
                        
                        <!-- Testimonial 2 -->
                        <div class="testimonial-slide">
                            <div class="testimonial-content">
                                <div class="rating">
                                    ★★★★★
                                </div>
                                <p>"Tempat yang cozy dengan makanan lezat. Pasta carbonara nya authentic banget! Harga juga sangat reasonable untuk kualitas yang diberikan."</p>
                                <h4>Budi Santoso</h4>
                                <small>Food Blogger</small>
                            </div>
                        </div>
                        
                        <!-- Testimonial 3 -->
                        <div class="testimonial-slide">
                            <div class="testimonial-content">
                                <div class="rating">
                                    ★★★★★
                                </div>
                                <p>"Dessert tiramisu nya benar-benar spesial. Sudah berkali-kali ke sini dan selalu puas. Sangat recommended untuk acara keluarga atau meeting bisnis."</p>
                                <h4>Maya Sari</h4>
                                <small>Business Executive</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="slider-nav">
                    <button class="slider-dot active" data-slide="0"></button>
                    <button class="slider-dot" data-slide="1"></button>
                    <button class="slider-dot" data-slide="2"></button>
                </div>
            </div>
        </div>
    </section>

    <!-- Reservation Section -->
    <section class="reservation" id="reservation">
        <div class="container">
            <div class="section-title">
                <h2>Reservasi Meja</h2>
                <p>Pesan meja Anda sekarang dan nikmati pengalaman kuliner yang tak terlupakan.</p>
            </div>
            
            <form class="reservation-form" id="reservationForm">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" class="form-control" id="reservationName" 
                               placeholder="Nama Lengkap" 
                               value="<?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : '' ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" id="reservationEmail" 
                               placeholder="Email"
                               value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="tel" class="form-control" id="reservationPhone" 
                               placeholder="Nomor Telepon" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control" id="reservationGuests" required>
                            <option value="" disabled selected>Jumlah Tamu</option>
                            <option value="1">1 Orang</option>
                            <option value="2">2 Orang</option>
                            <option value="3">3 Orang</option>
                            <option value="4">4 Orang</option>
                            <option value="5">5+ Orang</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <input type="date" class="form-control" id="reservationDate" 
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="time" class="form-control" id="reservationTime" required>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <textarea class="form-control" id="reservationNotes" 
                              placeholder="Pesan Khusus (alergi, perayaan khusus, dll.)" 
                              rows="4"></textarea>
                </div>
                
                <div class="form-group full-width">
                    <button type="submit" class="btn btn-block" id="reservationSubmit">
                        <i class="fas fa-calendar-check"></i> Reservasi Sekarang
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>TriyaskaFood</h3>
                    <p>Restoran dengan konsep modern yang menyajikan hidangan lezat dengan bahan-bahan terbaik dan pelayanan terbaik.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3>Link Cepat</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Beranda</a></li>
                        <li><a href="#about">Tentang Kami</a></li>
                        <li><a href="#menu">Menu</a></li>
                        <li><a href="#testimonials">Testimoni</a></li>
                        <li><a href="#reservation">Reservasi</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Kontak Kami</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> Kampus Ketintang Gedung K4, Program Vokasi Jalan Raya ketintang, Kec Gayungan, Kota Surabaya (60231)</li>
                        <li><i class="fas fa-phone"></i> +62 21 1234 5678</li>
                        <li><i class="fas fa-envelope"></i> info@triyaskafood.com</li>
                        <li><i class="fas fa-clock"></i> Buka Setiap Hari: 10.00 - 22.00</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 TriyaskaFood. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ==================== FULL JAVASCRIPT YANG SUDAH DIPERBAIKI ==================== -->
    <script>
    // Init semua sistem
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded with <?= count($menu_items) ?> menu items');
        initAllSystems();
    });
    
    function initAllSystems() {
        initMenuSystem();
        initCartSystem();
        initPaymentSystem(); // Tambah sistem pembayaran
        initAuthSystem();
        initModalSystem();
        initFilterSystem();
        initMobileMenu();
        initTestimonialSlider();
        initReservationForm();
        initNavbarScroll();
    }
    
    // 1. MENU SYSTEM
    function initMenuSystem() {
        console.log('Initializing menu system...');
        
        // Event delegation untuk semua button menu
        document.addEventListener('click', function(e) {
            // Decrease quantity
            if (e.target.closest('.qty-decrease')) {
                const btn = e.target.closest('.qty-decrease');
                const menuId = btn.dataset.menuId;
                decreaseQuantity(menuId);
            }
            
            // Increase quantity
            if (e.target.closest('.qty-increase')) {
                const btn = e.target.closest('.qty-increase');
                const menuId = btn.dataset.menuId;
                increaseQuantity(menuId);
            }
            
            // Add to cart
            if (e.target.closest('.add-to-cart-btn')) {
                const btn = e.target.closest('.add-to-cart-btn');
                addToCart(btn);
            }
        });
    }
    
    function decreaseQuantity(menuId) {
        const input = document.getElementById(`qty-${menuId}`);
        if (input && parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }
    
    function increaseQuantity(menuId) {
        const input = document.getElementById(`qty-${menuId}`);
        if (input && parseInt(input.value) < 10) {
            input.value = parseInt(input.value) + 1;
        }
    }
    
    function getQuantity(menuId) {
        const input = document.getElementById(`qty-${menuId}`);
        return input ? parseInt(input.value) || 1 : 1;
    }
    
    async function addToCart(btn) {
        console.log('Add to cart clicked');
        
        // Check if user is logged in
        if (!document.querySelector('.user-menu')) {
            showNotification('Silakan login terlebih dahulu!', 'warning');
            document.getElementById('login-modal').style.display = 'flex';
            return;
        }
        
        const menuId = btn.dataset.menuId;
        const menuName = btn.dataset.menuName;
        const menuPrice = btn.dataset.menuPrice;
        const quantity = getQuantity(menuId);
        
        console.log(`Adding to cart: ${menuName} (ID: ${menuId}, Qty: ${quantity})`);
        
        // Show loading
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambahkan...';
        btn.disabled = true;
        
        try {
            // Simpan ke localStorage untuk sementara
            const cart = getCart();
            const existingItem = cart.find(item => item.menuId == menuId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    menuId: menuId,
                    name: menuName,
                    price: parseFloat(menuPrice),
                    quantity: quantity
                });
            }
            
            saveCart(cart);
            updateCartDisplay();
            
            showNotification(`${menuName} (${quantity}x) ditambahkan ke keranjang!`, 'success');
            
            // Reset quantity
            const input = document.getElementById(`qty-${menuId}`);
            if (input) input.value = 1;
            
        } catch (error) {
            console.error('Error adding to cart:', error);
            showNotification('Gagal menambahkan ke keranjang', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
    
    // 2. CART SYSTEM
    function initCartSystem() {
        console.log('Initializing cart system...');
        
        // Cart icon click
        const cartIcon = document.getElementById('cartIcon');
        if (cartIcon) {
            cartIcon.addEventListener('click', function() {
                if (!document.querySelector('.user-menu')) {
                    showNotification('Silakan login terlebih dahulu!', 'warning');
                    document.getElementById('login-modal').style.display = 'flex';
                    return;
                }
                openCartModal();
            });
        }
        
        // Event delegation untuk tombol checkout
        document.addEventListener('click', function(e) {
            // Jika klik tombol checkout langsung
            if (e.target && e.target.id === 'checkout-btn') {
                e.preventDefault();
                processCheckout();
            }
            
            // Jika klik di dalam tombol checkout (icon atau text)
            if (e.target.closest && e.target.closest('#checkout-btn')) {
                e.preventDefault();
                processCheckout();
            }
        });
        
        // Update cart display
        updateCartDisplay();
    }
    
    function getCart() {
        try {
            const cartJson = localStorage.getItem('triyaskafood_cart') || '[]';
            return JSON.parse(cartJson);
        } catch (e) {
            return [];
        }
    }
    
    function saveCart(cart) {
        try {
            localStorage.setItem('triyaskafood_cart', JSON.stringify(cart));
        } catch (e) {
            console.error('Error saving cart:', e);
        }
    }
    
    function updateCartDisplay() {
        const cart = getCart();
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        
        // Update cart count
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
        }
        
        // Update cart total
        const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const cartTotal = document.getElementById('cartTotal');
        if (cartTotal) {
            cartTotal.textContent = `Total: Rp ${totalPrice.toLocaleString('id-ID')}`;
        }
    }
    
    // ==================== FUNGSI CHECKOUT DENGAN PEMBAYARAN ====================
    async function processCheckout() {
        console.log('Processing checkout...');
        
        // Check if user is logged in
        if (!document.querySelector('.user-menu')) {
            showNotification('Silakan login terlebih dahulu!', 'warning');
            document.getElementById('login-modal').style.display = 'flex';
            return;
        }
        
        const cart = getCart();
        if (cart.length === 0) {
            showNotification('Keranjang belanja kosong!', 'warning');
            return;
        }
        
        // Tampilkan modal pembayaran
        showPaymentModal(cart);
    }
    
    // 3. PAYMENT SYSTEM
    function initPaymentSystem() {
        console.log('Initializing payment system...');
        
        // Event listener untuk pilihan metode pembayaran
        const paymentMethodSelect = document.getElementById('payment-method');
        if (paymentMethodSelect) {
            paymentMethodSelect.addEventListener('change', function(e) {
                const method = e.target.value;
                const bankInfo = document.getElementById('bank-info');
                const ewalletInfo = document.getElementById('ewallet-info');
                const cashInfo = document.getElementById('cash-info');
                const uploadProof = document.getElementById('upload-proof-section');
                const proofInput = document.getElementById('payment-proof');
                
                // Reset semua
                bankInfo.style.display = 'none';
                ewalletInfo.style.display = 'none';
                cashInfo.style.display = 'none';
                
                if (method === 'transfer') {
                    bankInfo.style.display = 'block';
                    uploadProof.style.display = 'block';
                    proofInput.required = true;
                } else if (method === 'dana' || method === 'gopay' || method === 'ovo' || method === 'shopeepay') {
                    ewalletInfo.style.display = 'block';
                    uploadProof.style.display = 'block';
                    proofInput.required = true;
                } else if (method === 'cash') {
                    cashInfo.style.display = 'block';
                    uploadProof.style.display = 'none';
                    proofInput.required = false;
                } else {
                    uploadProof.style.display = 'block';
                    proofInput.required = true;
                }
                
                // Update steps
                updatePaymentSteps(1);
            });
        }
        
        // Preview bukti pembayaran
        const proofInput = document.getElementById('payment-proof');
        if (proofInput) {
            proofInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const preview = document.getElementById('proof-preview');
                
                if (file) {
                    if (file.size > 2 * 1024 * 1024) { // 2MB limit
                        showNotification('File terlalu besar! Maksimal 2MB', 'error');
                        e.target.value = '';
                        preview.innerHTML = '';
                        return;
                    }
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = `
                                <p><i class="fas fa-check-circle" style="color: #2ecc71;"></i> File terpilih: ${file.name}</p>
                                <img src="${e.target.result}" alt="Preview Bukti Pembayaran">
                                <p><small>Ukuran: ${(file.size / 1024).toFixed(1)} KB</small></p>
                            `;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.innerHTML = `
                            <p><i class="fas fa-file" style="color: #3498db;"></i> File terpilih: ${file.name}</p>
                            <p><small>(${file.type}) - ${(file.size / 1024).toFixed(1)} KB</small></p>
                        `;
                    }
                    
                    // Update steps
                    updatePaymentSteps(2);
                }
            });
        }
        
        // Handle form pembayaran
        const paymentForm = document.getElementById('payment-form');
        if (paymentForm) {
            paymentForm.addEventListener('submit', handlePaymentSubmit);
        }
    }
    
    function showPaymentModal(cart) {
        // Simpan cart data untuk digunakan nanti
        window.currentCart = cart;
        
        // Hitung total
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        // Tampilkan ringkasan
        let summaryHtml = `
            <div class="payment-summary">
                <h4><i class="fas fa-shopping-cart"></i> Ringkasan Pesanan</h4>
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <div>
                        <p style="font-size: 1.1rem; color: #2c3e50;"><strong>Total Pesanan:</strong></p>
                        <p style="font-size: 0.9rem; color: #666;">${cart.length} item</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 1.8rem; color: #e74c3c; font-weight: bold;">
                            Rp ${total.toLocaleString('id-ID')}
                        </p>
                    </div>
                </div>
                <div style="margin-top: 15px; max-height: 150px; overflow-y: auto; background: white; padding: 15px; border-radius: 8px;">
        `;
        
        cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            summaryHtml += `
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #f0f0f0;">
                    <div>
                        <p style="margin: 0; font-weight: 500;">${item.name}</p>
                        <p style="margin: 0; font-size: 0.85rem; color: #666;">${item.quantity} x Rp ${item.price.toLocaleString('id-ID')}</p>
                    </div>
                    <div style="font-weight: bold; color: #2c3e50;">
                        Rp ${itemTotal.toLocaleString('id-ID')}
                    </div>
                </div>
            `;
        });
        
        summaryHtml += `
                </div>
            </div>
            <div class="payment-steps">
                <div class="step active">
                    <div class="step-label">Pilih Pembayaran</div>
                </div>
                <div class="step">
                    <div class="step-label">Upload Bukti</div>
                </div>
                <div class="step">
                    <div class="step-label">Konfirmasi</div>
                </div>
            </div>
        `;
        
        document.getElementById('payment-summary').innerHTML = summaryHtml;
        
        // Reset form
        document.getElementById('payment-form').reset();
        document.getElementById('proof-preview').innerHTML = '';
        document.getElementById('bank-info').style.display = 'none';
        document.getElementById('ewallet-info').style.display = 'none';
        document.getElementById('cash-info').style.display = 'none';
        document.getElementById('upload-proof-section').style.display = 'block';
        
        // Update steps
        updatePaymentSteps(0);
        
        // Tampilkan modal
        document.getElementById('payment-modal').style.display = 'flex';
        document.getElementById('cart-modal').style.display = 'none';
    }
    
    function updatePaymentSteps(activeStep) {
        const steps = document.querySelectorAll('.payment-steps .step');
        steps.forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index < activeStep) {
                step.classList.add('completed');
            }
            if (index === activeStep) {
                step.classList.add('active');
            }
        });
    }
    
    // Fungsi konversi file ke base64
    function fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = () => resolve(reader.result);
            reader.onerror = error => reject(error);
        });
    }
    
    async function handlePaymentSubmit(e) {
        e.preventDefault();
        
        const cart = window.currentCart;
        if (!cart || cart.length === 0) {
            showNotification('Keranjang kosong!', 'error');
            return;
        }
        
        const paymentMethod = document.getElementById('payment-method').value;
        if (!paymentMethod) {
            showNotification('Pilih metode pembayaran!', 'error');
            return;
        }
        
        const paymentProofInput = document.getElementById('payment-proof');
        const paymentNotes = document.getElementById('payment-notes').value;
        
        // Validasi berdasarkan metode pembayaran
        let transactionId = '';
        
        if (paymentMethod === 'transfer') {
            const bankSelect = document.getElementById('bank-select').value;
            transactionId = document.getElementById('transaction-id').value;
            
            if (!bankSelect) {
                showNotification('Pilih bank tujuan!', 'error');
                return;
            }
            if (!transactionId) {
                showNotification('Masukkan ID transaksi!', 'error');
                return;
            }
            
            transactionId = bankSelect.toUpperCase() + '-' + transactionId;
        }
        
        if (paymentMethod === 'dana' || paymentMethod === 'gopay' || paymentMethod === 'ovo' || paymentMethod === 'shopeepay') {
            transactionId = document.getElementById('ewallet-transaction-id').value;
            if (!transactionId) {
                showNotification('Masukkan ID transaksi e-wallet!', 'error');
                return;
            }
            transactionId = paymentMethod.toUpperCase() + '-' + transactionId;
        }
        
        // Untuk cash, tidak perlu bukti
        if (paymentMethod !== 'cash') {
            if (!paymentProofInput.files[0]) {
                showNotification('Upload bukti pembayaran!', 'error');
                return;
            }
        }
        
        const confirmBtn = document.getElementById('confirm-payment-btn');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
        confirmBtn.disabled = true;
        
        try {
            // Convert bukti pembayaran ke base64 jika ada
            let paymentProofBase64 = null;
            if (paymentMethod !== 'cash' && paymentProofInput.files[0]) {
                paymentProofBase64 = await fileToBase64(paymentProofInput.files[0]);
            }
            
            // Prepare data untuk dikirim ke server
            const orderData = {
                items: cart.map(item => ({
                    menuId: parseInt(item.menuId),
                    name: item.name,
                    price: parseFloat(item.price),
                    quantity: parseInt(item.quantity)
                })),
                notes: document.getElementById('order-notes')?.value || '',
                payment_method: paymentMethod,
                payment_proof: paymentProofBase64,
                transaction_id: transactionId,
                payment_notes: paymentNotes
            };
            
            console.log('Sending order with payment data:', orderData);
            
            // Kirim ke server
            const response = await fetch('checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });
            
            const result = await response.json();
            console.log('Server response:', result);
            
            if (result.success) {
                showNotification(
                    `✅ Pesanan berhasil dibuat! Kode Order: ${result.order_code}`, 
                    'success'
                );
                
                // Clear cart
                saveCart([]);
                updateCartDisplay();
                
                // Close semua modal
                document.getElementById('payment-modal').style.display = 'none';
                document.getElementById('cart-modal').style.display = 'none';
                
                // Reset form
                document.getElementById('payment-form').reset();
                document.getElementById('proof-preview').innerHTML = '';
                
                // Redirect ke dashboard setelah 3 detik
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 3000);
                
            } else {
                throw new Error(result.message);
            }
            
        } catch (error) {
            console.error('Payment error:', error);
            showNotification('❌ Gagal memproses pesanan: ' + error.message, 'error');
        } finally {
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        }
    }
    
    function openCartModal() {
        const cart = getCart();
        const modal = document.getElementById('cart-modal');
        
        if (cart.length === 0) {
            document.getElementById('cartItemsContainer').innerHTML = `
                <div class="empty-cart-message">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Keranjang belanja kosong</p>
                </div>
            `;
        } else {
            let html = '';
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                html += `
                    <div class="cart-item">
                        <div class="cart-item-details">
                            <h4>${item.name}</h4>
                            <p class="item-price">Rp ${item.price.toLocaleString('id-ID')} x ${item.quantity}</p>
                            <div class="cart-quantity-controls">
                                <button class="cart-qty-btn cart-minus" onclick="updateCartItem(${index}, -1)">-</button>
                                <span class="cart-quantity">${item.quantity}</span>
                                <button class="cart-qty-btn cart-plus" onclick="updateCartItem(${index}, 1)">+</button>
                                <button class="cart-remove-btn" onclick="removeCartItem(${index})">Hapus</button>
                            </div>
                        </div>
                        <div class="cart-item-total">
                            <span>Rp ${itemTotal.toLocaleString('id-ID')}</span>
                        </div>
                    </div>
                `;
            });
            document.getElementById('cartItemsContainer').innerHTML = html;
        }
        
        updateCartDisplay();
        modal.style.display = 'flex';
    }
    
    function updateCartItem(index, change) {
        const cart = getCart();
        if (cart[index]) {
            cart[index].quantity += change;
            if (cart[index].quantity < 1) {
                cart.splice(index, 1);
            }
            saveCart(cart);
            updateCartDisplay();
            openCartModal();
            showNotification('Keranjang diperbarui', 'success');
        }
    }
    
    function removeCartItem(index) {
        if (confirm('Hapus item dari keranjang?')) {
            const cart = getCart();
            cart.splice(index, 1);
            saveCart(cart);
            updateCartDisplay();
            openCartModal();
            showNotification('Item dihapus dari keranjang', 'success');
        }
    }
    
    // 4. AUTH SYSTEM
    function initAuthSystem() {
        console.log('Initializing auth system...');
        
        const loginBtn = document.getElementById('login-btn');
        const registerBtn = document.getElementById('register-btn');
        
        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                document.getElementById('login-modal').style.display = 'flex';
            });
        }
        
        if (registerBtn) {
            registerBtn.addEventListener('click', () => {
                document.getElementById('register-modal').style.display = 'flex';
            });
        }
        
        // Handle form submissions
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        
        if (loginForm) {
            loginForm.addEventListener('submit', handleLogin);
        }
        
        if (registerForm) {
            registerForm.addEventListener('submit', handleRegister);
        }
    }
    
    async function handleLogin(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(form);
            const response = await fetch('auth.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Login berhasil!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(result.message || 'Login gagal', 'error');
            }
        } catch (error) {
            showNotification('Koneksi error: ' + error.message, 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    async function handleRegister(e) {
        e.preventDefault();
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftarkan...';
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(form);
            const response = await fetch('auth.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Pendaftaran berhasil!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(result.message || 'Pendaftaran gagal', 'error');
            }
        } catch (error) {
            showNotification('Koneksi error: ' + error.message, 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    // 5. MODAL SYSTEM
    function initModalSystem() {
        console.log('Initializing modal system...');
        
        // Close buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            });
        });
        
        // Close on outside click
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
    }
    
    // 6. FILTER SYSTEM
    function initFilterSystem() {
        console.log('Initializing filter system...');
        
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                document.querySelectorAll('.menu-item').forEach(item => {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }
    
    // 7. MOBILE MENU
    function initMobileMenu() {
        console.log('Initializing mobile menu...');
        
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
            });
        }
    }
    
    // 8. TESTIMONIAL SLIDER
    function initTestimonialSlider() {
        console.log('Initializing testimonial slider...');
        
        const track = document.getElementById('testimonialTrack');
        const dots = document.querySelectorAll('.slider-dot');
        let currentSlide = 0;
        
        if (!track || dots.length === 0) return;
        
        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            track.style.transform = `translateX(-${slideIndex * 100}%)`;
            
            // Update dots
            dots.forEach((dot, index) => {
                if (index === slideIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        // Dot click events
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToSlide(index);
            });
        });
        
        // Auto slide every 5 seconds
        setInterval(() => {
            currentSlide = (currentSlide + 1) % dots.length;
            goToSlide(currentSlide);
        }, 5000);
    }
    
    // 9. RESERVATION FORM
    function initReservationForm() {
        console.log('Initializing reservation form...');
        
        const reservationForm = document.getElementById('reservationForm');
        if (!reservationForm) return;
        
        // Set min date to today
        const dateInput = document.getElementById('reservationDate');
        if (dateInput) {
            dateInput.min = new Date().toISOString().split('T')[0];
        }
        
        reservationForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('reservationSubmit');
            const originalText = submitBtn.innerHTML;
            
            // Check if user is logged in
            if (!document.querySelector('.user-menu')) {
                showNotification('Silakan login terlebih dahulu untuk membuat reservasi!', 'warning');
                document.getElementById('login-modal').style.display = 'flex';
                return;
            }
            
            // Get form data
            const reservationData = {
                name: document.getElementById('reservationName').value,
                email: document.getElementById('reservationEmail').value,
                phone: document.getElementById('reservationPhone').value,
                guests: document.getElementById('reservationGuests').value,
                date: document.getElementById('reservationDate').value,
                time: document.getElementById('reservationTime').value,
                notes: document.getElementById('reservationNotes').value
            };
            
            // Validation
            if (!reservationData.name || !reservationData.email || !reservationData.phone || 
                !reservationData.guests || !reservationData.date || !reservationData.time) {
                showNotification('Harap isi semua field yang wajib!', 'error');
                return;
            }
            
            // Show loading
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitBtn.disabled = true;
            
            try {
                // Simulate API call
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                showNotification('Reservasi berhasil dikirim! Kami akan menghubungi Anda untuk konfirmasi.', 'success');
                
                // Reset form
                reservationForm.reset();
                
                // Reset date min
                if (dateInput) {
                    dateInput.min = new Date().toISOString().split('T')[0];
                }
                
            } catch (error) {
                showNotification('Gagal mengirim reservasi. Silakan coba lagi.', 'error');
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    }
    
    // 10. NAVBAR SCROLL EFFECT
    function initNavbarScroll() {
        console.log('Initializing navbar scroll...');
        
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
    
    // NOTIFICATION SYSTEM
    function showNotification(message, type = 'info') {
        const existing = document.querySelectorAll('.notification');
        existing.forEach(notif => notif.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
    
    function getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    </script>
</body>
</html>