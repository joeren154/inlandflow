<?php
require_once '../config/database.php';

try {
    $db->exec("ALTER TABLE tb_placed_order ADD COLUMN room_fee DECIMAL(10,2) DEFAULT 0 AFTER kids_fee");
} catch (Exception $e) {
    // Column may already exist, ignore
}

try {
    $db->exec("ALTER TABLE tb_placed_order ADD COLUMN num_days INT(11) DEFAULT 1 AFTER room_fee");
} catch (Exception $e) {
    // Column may already exist, ignore
}

echo "Migration completed!";