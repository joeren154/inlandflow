<?php
require_once '../config/database.php';

$updatebesttop = $db->prepare("UPDATE tb_resort SET isFeatured = ?, isBestSeller = ?, isTopItem = ?, isPromoDeals = ?, isOnSale = ? WHERE resortid = ?");
$updatebesttop->execute([$_POST['isFeatured'], $_POST['isBestSeller'], $_POST['isTopItem'], $_POST['isPromoDeals'], $_POST['isOnSale'], $_POST['resortid']]);

echo "OK";