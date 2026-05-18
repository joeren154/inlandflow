<?php
require_once '../config/database.php';

if(isset($_SESSION['type_of_user'])) {
    $TypeofUser = $_SESSION['type_of_user'];
} else {
    $TypeofUser = " ";
}

if($TypeofUser == "Guest") {
    $guest_id = $_SESSION['user_reg'];
    $sqlupdate = "UPDATE tb_guest SET Password = ? WHERE guest_id = ?";
    $db->prepare($sqlupdate)->execute([$_POST['new_pass'], $guest_id]);
    echo "Password Successfully Updated";
} else if($TypeofUser == "Resort") {
    $resortid = $_SESSION['user_reg'];
    $sqlupdate = "UPDATE tb_resort SET password = ? WHERE resortid = ?";
    $db->prepare($sqlupdate)->execute([$_POST['new_pass'], $resortid]);
    echo "Password Successfully Updated";
} else if($TypeofUser == "Municipal") {
    $id = $_SESSION['user_reg'];
    $sqlupdate = "UPDATE tb_municipality SET password = ? WHERE id = ?";
    $db->prepare($sqlupdate)->execute([$_POST['new_pass'], $id]);
    echo "Password Successfully Updated";
} else if($TypeofUser == "Provincial") {
    $provid = $_SESSION['user_reg'];
    $sqlupdate = "UPDATE tb_provincial SET password = ? WHERE provid = ?";
    $db->prepare($sqlupdate)->execute([$_POST['new_pass'], $provid]);
    echo "Password Successfully Updated";
}