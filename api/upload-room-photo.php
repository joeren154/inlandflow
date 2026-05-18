<?php
require_once '../config/database.php';

$response = ['status' => 'error', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit'])) {
    $response['message'] = 'Invalid request';
    echo json_encode($response);
    exit;
}

$resortid = $_SESSION['user_reg'] ?? 0;
$resort_room_id = (int)($_POST['resort_room_id'] ?? 0);

if (!$resortid || !$resort_room_id) {
    $response['message'] = 'Missing resort or room ID';
    echo json_encode($response);
    exit;
}

$check = $db->prepare("SELECT resort_room_id FROM tb_resort_room WHERE resort_room_id = ? AND resortid = ?");
$check->execute([$resort_room_id, $resortid]);
if (!$check->fetch()) {
    $response['message'] = 'Room not found';
    echo json_encode($response);
    exit;
}

$fileDestination = '../uploads_flow/';
$files = $_FILES['files'];
$filenum = $files['name'];

if (empty($filenum) || empty($filenum[0])) {
    $response['message'] = 'Select files to upload';
    echo json_encode($response);
    exit;
}

$uploaded = 0;
$nextId = $db->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM images")->fetch()['next_id'];

$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

foreach ($filenum as $key => $val) {
    if ($files['error'][$key] !== 0) continue;

    $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) continue;

    $safeName = $resortid . '_room_' . $resort_room_id . '_' . time() . '_' . $key . '.' . $ext;
    $filePath = $fileDestination . $safeName;

    if (move_uploaded_file($files['tmp_name'][$key], $filePath)) {
        $insert = $db->prepare("INSERT INTO images(id, resortid, resort_room_id, file_name) VALUES (?, ?, ?, ?)");
        $insert->execute([$nextId, $resortid, $resort_room_id, $safeName]);
        $nextId++;
        $uploaded++;
    }
}

if ($uploaded > 0) {
    $response['status'] = 'success';
    $response['message'] = $uploaded . ' image(s) uploaded successfully';
} else {
    $response['message'] = 'No files were uploaded';
}

echo json_encode($response);
