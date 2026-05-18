<?php
require_once '../config/database.php';

$deleteRoom = $db->prepare("DELETE FROM tb_resort_room WHERE resort_room_id = ?");
$deleteRoom->execute([$_POST['resort_room_id']]);
echo "OK";