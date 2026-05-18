<?php
require_once '../config/database.php';

if(isset($_POST['submit'])) { 
    $resortid = $_SESSION['user_reg'];
    $fileDestination = '../uploads_flow/';
    $files = $_FILES['files'];
    $filenum = $_FILES['files']['name'];

    $fileSize = $_FILES['files']['size'];
    $fileError = $_FILES['files']['error'];
    $fileType = $_FILES['files']['type'];
        
    if(!empty($filenum)) {
        $nextId = $db->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM images")->fetch()['next_id'];
        foreach($filenum as $key => $val) {
            $filePath = $fileDestination . $val;
            move_uploaded_file($_FILES['files']['tmp_name'][$key], $filePath);
            $sqlsaveimage = "INSERT INTO images(id, resortid, file_name)VALUES('$nextId', '$resortid', '$val')";
            $statement = $db->prepare($sqlsaveimage);
            $statement->execute();
            $nextId++;
        }
        header('Location: ../index.php?page=resort-gallery');
        exit;
    } else if(empty($filenum)) {
        echo "<script>alert('Select File to Upload');</script>";
        header('Location: ../index.php?page=resort-gallery');
        exit;
    }
}
