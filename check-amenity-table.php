<?php
require_once 'config/database.php';

echo "=== Checking tb_add_on_amenities table structure ===\n\n";

$stmt = $db->query("DESCRIBE tb_add_on_amenities");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Columns in tb_add_on_amenities:\n";
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})";
    if ($col['Key'] === 'PRI') echo " [PRIMARY KEY]";
    if ($col['Extra'] === 'auto_increment') echo " [AUTO_INCREMENT]";
    echo "\n";
}

echo "\n=== Last 5 entries ===\n";
$lastEntries = $db->query("SELECT * FROM tb_add_on_amenities ORDER BY add_on_amenity_id DESC LIMIT 5");
while ($row = $lastEntries->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>