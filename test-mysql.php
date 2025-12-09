<?php
// test-mysql.php
echo "<h2>🔍 Testing MySQL Connection on InfinityFree</h2>";

$tests = [
    ['host' => 'localhost', 'user' => 'if0_40586811', 'pass' => 'Anarus12'],
    ['host' => '127.0.0.1', 'user' => 'if0_40586811', 'pass' => 'Anarus12'],
    ['host' => 'sqlXXX.epizy.com', 'user' => 'epiz_xxxxxx', 'pass' => 'xxxxxx'],
    ['host' => 'localhost:3306', 'user' => 'if0_40586811', 'pass' => 'Anarus12'],
];

foreach ($tests as $test) {
    echo "<h3>Testing: {$test['user']}@{$test['host']}</h3>";
    
    $conn = @mysqli_connect($test['host'], $test['user'], $test['pass']);
    
    if ($conn) {
        echo "<p style='color:green;'>✅ MySQL CONNECTED!</p>";
        
        // List databases
        $result = mysqli_query($conn, "SHOW DATABASES");
        echo "<p>Available databases:</p>";
        echo "<ul>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
        
        mysqli_close($conn);
    } else {
        echo "<p style='color:red;'>❌ Failed: " . mysqli_connect_error() . "</p>";
    }
    echo "<hr>";
}
?>