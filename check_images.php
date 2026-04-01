<?php
require_once 'config/database.php';
$stmt = $pdo->query("SELECT id, name, image FROM products LIMIT 20");
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Image Path</th><th>File Exists?</th></tr>";
foreach ($stmt->fetchAll() as $row) {
    $path = "uploads/products/" . $row['image'];
    $exists = file_exists($path) ? "YES" : "NO";
    $asset_path = "assets/img/product/" . $row['image'];
    $asset_exists = file_exists($asset_path) ? "YES (in assets)" : "NO";
    echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['image']}</td><td>{$exists} / {$asset_exists}</td></tr>";
}
echo "</table>";
?>
