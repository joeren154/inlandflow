<?php
require_once '../config/database.php';

$iddelete = (int)($_POST['imageid'] ?? 0);

$stmt = $db->prepare("SELECT file_name FROM images WHERE id = ?");
$stmt->execute([$iddelete]);
$image = $stmt->fetch();

$deleteResortPhoto = $db->prepare("DELETE FROM images WHERE id = ?");
$deleteResortPhoto->execute([$iddelete]);

if ($image) {
    $filePath = '../uploads_flow/' . $image['file_name'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

echo "OK";