<?php
require_once '../config/database.php';

$iddelete = $_POST['cart_id'];

$deletefromCart = $db->prepare("DELETE FROM tb_cart WHERE cart_id = ?");
$deletefromCart->execute([$iddelete]);

echo "OK";