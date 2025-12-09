<?php
// setup-sqlite.php - FIXED VERSION
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup SQLite Database - Triyaska Food</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        code { background: #eee; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠 Setup SQLite Database - FIXED</h1>
        <p>Ini akan membuat database SQLite dengan struktur yang benar.</p>
        
        <?php
        // Step 0: Hapus database lama jika ada
        echo "<div class='step'>";
        echo "<h3>Step 0: Reset Database Lama</h3>";
        
        $dataDir = __DIR__ . '/data';
        $dbFile = $dataDir . '/triyaskafood.db';
        
        if (file_exists($dbFile)) {
            if (unlink($dbFile)) {
                echo "<div class='success'>✅ Database lama berhasil dihapus</div>";
            } else {
                echo "<div class='error'>❌ Gagal menghapus database lama</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ Tidak ada database lama</div>";
        }
        echo "</div>";
        
        // Step 1: Buat folder data
        echo "<div class='step'>";
        echo "<h3>Step 1: Membuat Folder Data</h3>";
        
        if (!file_exists($dataDir)) {
            if (mkdir($dataDir, 0755, true)) {
                echo "<div class='success'>✅ Folder 'data' berhasil dibuat</div>";
            } else {
                echo "<div class='error'>❌ Gagal membuat folder 'data'</div>";
                die();
            }
        } else {
            echo "<div class='info'>ℹ️ Folder 'data' sudah ada</div>";
        }
        echo "</div>";
        
        // Step 2: Buat koneksi database
        echo "<div class='step'>";
        echo "<h3>Step 2: Membuat Koneksi Database</h3>";
        
        try {
            $pdo = new PDO("sqlite:$dbFile");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='success'>✅ Koneksi database berhasil</div>";
            echo "<p>File: <code>" . basename($dbFile) . "</code></p>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Gagal koneksi: " . $e->getMessage() . "</div>";
            die();
        }
        echo "</div>";
        
        // Step 3: Buat tabel dengan struktur yang benar
        echo "<div class='step'>";
        echo "<h3>Step 3: Membuat Tabel dengan Struktur yang Benar</h3>";
        
        // DROP semua tabel lama jika ada
        $tables = ['users', 'products', 'cart', 'orders', 'order_items', 'payments', 'reservations'];
        foreach ($tables as $table) {
            try {
                $pdo->exec("DROP TABLE IF EXISTS $table");
            } catch(Exception $e) {
                // Ignore errors
            }
        }
        
        // Buat tabel USERS dengan struktur yang benar
        $sql_users = "CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,  -- Nama kolom: password, bukan password_hash
            full_name TEXT,
            phone TEXT,
            address TEXT,
            role TEXT DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $pdo->exec($sql_users);
            echo "<div class='success'>✅ Tabel 'users' berhasil dibuat</div>";
            echo "<p>Kolom: id, username, email, <strong>password</strong>, full_name, phone, address, role, created_at</p>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tabel users: " . $e->getMessage() . "</div>";
            die();
        }
        
        // Buat tabel PRODUCTS
        $sql_products = "CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            category TEXT,
            image_url TEXT,
            is_available INTEGER DEFAULT 1,
            stock INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $pdo->exec($sql_products);
            echo "<div class='success'>✅ Tabel 'products' berhasil dibuat</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tabel products: " . $e->getMessage() . "</div>";
        }
        
        // Buat tabel CART
        $sql_cart = "CREATE TABLE cart (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER DEFAULT 1,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $pdo->exec($sql_cart);
            echo "<div class='success'>✅ Tabel 'cart' berhasil dibuat</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tabel cart: " . $e->getMessage() . "</div>";
        }
        
        // Buat tabel ORDERS
        $sql_orders = "CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_code TEXT UNIQUE NOT NULL,
            user_id INTEGER NOT NULL,
            total_amount REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            delivery_address TEXT,
            notes TEXT
        )";
        
        try {
            $pdo->exec($sql_orders);
            echo "<div class='success'>✅ Tabel 'orders' berhasil dibuat</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tabel orders: " . $e->getMessage() . "</div>";
        }
        
        // Buat tabel ORDER_ITEMS
        $sql_order_items = "CREATE TABLE order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            unit_price REAL NOT NULL,
            subtotal REAL NOT NULL
        )";
        
        try {
            $pdo->exec($sql_order_items);
            echo "<div class='success'>✅ Tabel 'order_items' berhasil dibuat</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tabel order_items: " . $e->getMessage() . "</div>";
        }
        
        // Buat tabel PAYMENTS
        $sql_payments = "CREATE TABLE payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            payment_method TEXT NOT NULL,
            amount REAL NOT NULL,
            status TEXT DEFAULT 'pending',
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            transaction_id TEXT,
            notes TEXT
        )";
        
        try {
            $pdo->exec($sql_payments);
            echo "<div class='success'>✅ Tabel 'payments' berhasil dibuat</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tabel payments: " . $e->getMessage() . "</div>";
        }
        
        // Buat tabel RESERVATIONS
        $sql_reservations = "CREATE TABLE reservations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            reservation_date TEXT NOT NULL,
            reservation_time TEXT NOT NULL,
            party_size INTEGER NOT NULL,
            table_number TEXT,
            status TEXT DEFAULT 'pending',
            special_requests TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $pdo->exec($sql_reservations);
            echo "<div class='success'>✅ Tabel 'reservations' berhasil dibuat</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tabel reservations: " . $e->getMessage() . "</div>";
        }
        
        echo "<div class='success'>🎯 Semua tabel berhasil dibuat!</div>";
        echo "</div>";
        
        // Step 4: Tambah data default
        echo "<div class='step'>";
        echo "<h3>Step 4: Menambahkan Data Default</h3>";
        
        // Tambah admin user - PERBAIKAN: gunakan kolom 'password' bukan 'password_hash'
        try {
            // Cek dulu apakah admin sudah ada
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                // Hash password
                $password_hashed = password_hash('admin123', PASSWORD_DEFAULT);
                
                // Insert admin - PERHATIAN: kolomnya 'password', bukan 'password_hash'
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) 
                                     VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['admin', 'admin@triyaskafood.com', $password_hashed, 'Administrator', 'admin']);
                
                echo "<div class='success'>✅ User admin berhasil ditambahkan</div>";
                echo "<p><strong>Username:</strong> admin</p>";
                echo "<p><strong>Password:</strong> admin123</p>";
                echo "<p><strong>Email:</strong> admin@triyaskafood.com</p>";
                echo "<p><strong>Role:</strong> admin</p>";
            } else {
                echo "<div class='info'>ℹ️ User admin sudah ada</div>";
            }
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tambah admin: " . $e->getMessage() . "</div>";
            echo "<p>Error detail: " . print_r($pdo->errorInfo(), true) . "</p>";
        }
        
        // Tambah sample user customer
        try {
            $password_hashed = password_hash('customer123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (username, email, password, full_name, role) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['customer', 'customer@example.com', $password_hashed, 'John Customer', 'customer']);
            echo "<div class='success'>✅ User customer berhasil ditambahkan</div>";
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Error tambah customer: " . $e->getMessage() . "</div>";
        }
        
        // Tambah sample products
        $products = [
            ['Nasi Goreng Spesial', 'Nasi goreng dengan telur, ayam, dan sayuran', 25000, 'Makanan'],
            ['Mie Goreng Jawa', 'Mie goreng khas Jawa dengan bumbu rempah', 22000, 'Makanan'],
            ['Es Teh Manis', 'Es teh dengan gula spesial', 5000, 'Minuman'],
            ['Ayam Bakar', 'Ayam bakar bumbu spesial', 35000, 'Makanan'],
            ['Sate Ayam', 'Sate ayam dengan bumbu kacang', 30000, 'Makanan'],
            ['Jus Alpukat', 'Jus alpukat segar', 15000, 'Minuman'],
            ['Nasi Padang', 'Nasi dengan lauk pauk khas Padang', 30000, 'Makanan'],
            ['Kopi Hitam', 'Kopi hitam pilihan', 8000, 'Minuman']
        ];
        
        $productCount = 0;
        foreach ($products as $product) {
            list($name, $desc, $price, $category) = $product;
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $desc, $price, $category]);
                $productCount++;
            } catch(PDOException $e) {
                echo "<div class='error'>❌ Error tambah produk $name: " . $e->getMessage() . "</div>";
            }
        }
        
        echo "<div class='success'>✅ $productCount produk sample berhasil ditambahkan</div>";
        echo "</div>";
        
        // Step 5: Verifikasi
        echo "<div class='step'>";
        echo "<h3>Step 5: Verifikasi Database</h3>";
        
        try {
            // Hitung users
            $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
            $user_count = $stmt->fetch()['user_count'];
            
            // Hitung products
            $stmt = $pdo->query("SELECT COUNT(*) as product_count FROM products");
            $product_count = $stmt->fetch()['product_count'];
            
            // Tampilkan struktur tabel users
            $stmt = $pdo->query("PRAGMA table_info(users)");
            $columns = $stmt->fetchAll();
            
            echo "<div class='success'>";
            echo "<h4>✅ Database berhasil diverifikasi!</h4>";
            echo "<p>👥 Total Users: $user_count</p>";
            echo "<p>🍔 Total Products: $product_count</p>";
            echo "<p>💾 File: " . basename($dbFile) . " (" . round(filesize($dbFile)/1024, 2) . " KB)</p>";
            echo "</div>";
            
            echo "<div class='info'>";
            echo "<h4>Struktur Tabel Users:</h4>";
            echo "<table border='1' cellpadding='5' style='width:100%; border-collapse:collapse;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Type</th><th>Not Null</th><th>Default</th><th>PK</th></tr>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>" . $col['cid'] . "</td>";
                echo "<td><strong>" . $col['name'] . "</strong></td>";
                echo "<td>" . $col['type'] . "</td>";
                echo "<td>" . $col['notnull'] . "</td>";
                echo "<td>" . ($col['dflt_value'] ?: 'NULL') . "</td>";
                echo "<td>" . $col['pk'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
            // Tampilkan data users
            $stmt = $pdo->query("SELECT id, username, email, role FROM users");
            $users = $stmt->fetchAll();
            
            echo "<div class='info'>";
            echo "<h4>Data Users:</h4>";
            echo "<table border='1' cellpadding='5' style='width:100%; border-collapse:collapse;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
            
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Verifikasi gagal: " . $e->getMessage() . "</div>";
        }
        echo "</div>";
        
        // Step 6: Test login
        echo "<div class='step'>";
        echo "<h3>Step 6: Test Login</h3>";
        
        try {
            // Test query untuk login
            $test_user = 'admin';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$test_user]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo "<div class='success'>";
                echo "✅ Test query berhasil!<br>";
                echo "User ditemukan: " . htmlspecialchars($user['username']) . "<br>";
                echo "Password hash tersimpan: " . (strlen($user['password']) > 10 ? '✅' : '❌');
                echo "</div>";
                
                // Test password verification
                $test_password = 'admin123';
                if (password_verify($test_password, $user['password'])) {
                    echo "<div class='success'>✅ Password verification BERHASIL!</div>";
                } else {
                    echo "<div class='error'>❌ Password verification GAGAL!</div>";
                }
            } else {
                echo "<div class='error'>❌ User tidak ditemukan</div>";
            }
        } catch(PDOException $e) {
            echo "<div class='error'>❌ Test gagal: " . $e->getMessage() . "</div>";
        }
        echo "</div>";
        
        echo "<div style='text-align: center; margin-top: 30px; padding: 30px; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-radius: 10px;'>";
        echo "<h2 style='color: #155724;'>🎉 SETUP SELESAI!</h2>";
        echo "<p style='font-size: 1.2em;'>Database SQLite berhasil dibuat dan siap digunakan.</p>";
        echo "<p style='margin: 20px 0;'>";
        echo "<a href='index.php' class='btn btn-success' style='font-size: 1.1em; padding: 15px 30px;'>🚀 Buka Aplikasi</a> ";
        echo "<a href='login.php' class='btn' style='font-size: 1.1em; padding: 15px 30px;'>🔑 Login Admin</a>";
        echo "</p>";
        echo "<p><strong>Login Admin:</strong> admin / admin123</p>";
        echo "</div>";
        ?>
    </div>
</body>
</html>