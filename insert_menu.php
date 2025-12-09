<?php
// insert_menu.php
require_once 'config.php';

echo "<h2>Insert Menu Data</h2>";

$menuItems = [
    ['Nasi Goreng Spesial', 'Nasi goreng dengan ayam, udang, telur, dan sayuran segar', 35000, 'main', 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=500&auto=format&fit=crop'],
    ['Mie Ayam Jamur', 'Mie ayam dengan jamur tiram, pangsit goreng, dan kuah kaldu', 30000, 'main', 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=500&auto=format&fit=crop'],
    ['Ayam Bakar Madu', 'Ayam bakar dengan bumbu madu spesial, lalapan, dan sambal', 45000, 'main', 'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=500&auto=format&fit=crop'],
    ['Sate Ayam 10 Tusuk', 'Sate ayam dengan bumbu kacang dan lontong', 40000, 'main', 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=500&auto=format&fit=crop'],
    ['Rendang Sapi', 'Rendang sapi asli Padang dengan bumbu rempah pilihan', 55000, 'main', 'https://images.unsplash.com/photo-1631700611307-37dbcb89ef7e?w=500&auto=format&fit=crop'],
    ['Lumpia Udang', 'Lumpia isi udang dan sayuran dengan saus spesial', 25000, 'appetizer', 'https://images.unsplash.com/photo-1563379091339-03246963d9d6?w=500&auto=format&fit=crop'],
    ['Siomay Bandung', 'Siomay ikan dengan bumbu kacang dan tahu', 20000, 'appetizer', 'https://images.unsplash.com/photo-1541519227354-08fa5d50c44d?w=500&auto=format&fit=crop'],
    ['Tahu Crispy', 'Tahu goreng dengan tepung krispi', 15000, 'appetizer', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&auto=format&fit=crop'],
    ['Es Teh Manis', 'Es teh dengan gula pasir', 8000, 'drink', 'https://images.unsplash.com/photo-1561122569-4668e76dd6e9?w=500&auto=format&fit=crop'],
    ['Jus Alpukat', 'Jus alpukat segar dengan susu kental manis', 15000, 'drink', 'https://images.unsplash.com/photo-1629367494173-c78a56567877?w=500&auto=format&fit=crop'],
    ['Kopi Latte', 'Kopi espresso dengan susu steamed', 20000, 'drink', 'https://images.unsplash.com/photo-1570968915860-54d5c301fa9f?w=500&auto=format&fit=crop'],
    ['Es Krim Coklat', 'Es krim homemade rasa coklat Belgia', 18000, 'dessert', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=500&auto=format&fit=crop'],
    ['Puding Mangga', 'Puding mangga segar dengan saus caramel', 22000, 'dessert', 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?w=500&auto=format&fit=crop'],
    ['Brownies Keju', 'Brownies lembut dengan topping keju cheddar', 25000, 'dessert', 'https://images.unsplash.com/photo-1606313564200-75f2d4fa383b?w=500&auto=format&fit=crop']
];

try {
    // Clear existing data
    $pdo->exec("DELETE FROM menu");
    echo "<p>✅ Cleared existing menu data</p>";
    
    // Insert new data
    $stmt = $pdo->prepare("INSERT INTO menu (name, description, price, category, image_url, is_available) 
                          VALUES (?, ?, ?, ?, ?, 1)");
    
    $count = 0;
    foreach ($menuItems as $item) {
        $stmt->execute($item);
        $count++;
    }
    
    echo "<p>✅ Inserted $count menu items</p>";
    
    // Verify
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM menu");
    $result = $stmt->fetch();
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>✅ SUCCESS!</h3>";
    echo "<p>Total menu items in database: <strong>" . $result['total'] . "</strong></p>";
    echo "<p><a href='index.php' style='color: #0c5460; font-weight: bold; text-decoration: none;'>← Go to Homepage</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ ERROR</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Check database connection and table structure</p>";
    echo "</div>";
}
?>