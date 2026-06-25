<?php
require_once 'functions.php';

echo "<h1>Tax Debug</h1>";

// Check all products with their tax values
$products = getAllProductsAdmin();
echo "<h2>All Products in Database:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Price</th><th>tax_mpesa</th><th>tax_bank</th></tr>";
foreach($products as $p) {
    echo "<tr>";
    echo "<td>" . $p['id'] . "</td>";
    echo "<td>" . $p['name'] . "</td>";
    echo "<td>" . $p['price'] . "</td>";
    echo "<td>" . ($p['tax_mpesa'] ?? 'NULL') . "</td>";
    echo "<td>" . ($p['tax_bank'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check if columns exist
echo "<h2>Column Check:</h2>";
$db = getDB();
$stmt = $db->query("SHOW COLUMNS FROM products");
echo "<pre>";
print_r($stmt->fetchAll());
echo "</pre>";
?>