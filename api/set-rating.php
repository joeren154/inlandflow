<?php
require_once '../config/database.php';

$reservation_status = "Reviewed";

$updateStatus = $db->prepare("UPDATE tb_placed_order SET reservation_status = ?, ratings = ?, rating_comment = ? WHERE po_id = ?");
$updateStatus->execute([$reservation_status, $_POST['ratings'], $_POST['rating_comment'], $_POST['po_id']]);

echo "OK";