<?php
require_once '../config/database.php';

if(isset($_SESSION['type_of_user'])) {
    $TypeofUser = $_SESSION['type_of_user'];
    $username = $_POST['username'];
} else {
    $TypeofUser = " ";
}

if($TypeofUser == "Guest") {
    $guest_id = $_SESSION['user_reg'];
    $sqlduplicateuser = $db->prepare("SELECT * FROM tb_guest WHERE guest_id != '$guest_id' AND Username = '$username'");
    $sqlduplicateuser->execute();
    $rowuser = $sqlduplicateuser->fetch(PDO::FETCH_ASSOC);

    if($sqlduplicateuser->rowCount() > 0) {
        echo "Duplication is Prohibited! \n Please Find another Username";
    } else {
        $sqlupdate = "UPDATE tb_guest SET Username = ? WHERE guest_id = ?";
        $db->prepare($sqlupdate)->execute([$username, $guest_id]);
        echo "Username Successfully Updated";
    }
} else if($TypeofUser == "Resort") {
    $resortid = $_SESSION['user_reg'];
    $sqlduplicateuser = $db->prepare("SELECT * FROM tb_resort WHERE resortid != '$resortid' AND username = '$username'");
    $sqlduplicateuser->execute();
    $rowuser = $sqlduplicateuser->fetch(PDO::FETCH_ASSOC);

    if($sqlduplicateuser->rowCount() > 0) {
        echo "Duplication is Prohibited! \n Please Find another Username";
    } else {
        $sqlupdate = "UPDATE tb_resort SET username = ? WHERE resortid = ?";
        $db->prepare($sqlupdate)->execute([$username, $resortid]);
        echo "Username Successfully Updated";
    }
} else if($TypeofUser == "Municipal") {
    $id = $_SESSION['user_reg'];
    $sqlduplicateuser = $db->prepare("SELECT * FROM tb_municipality WHERE id != '$id' AND username = '$username'");
    $sqlduplicateuser->execute();
    $rowuser = $sqlduplicateuser->fetch(PDO::FETCH_ASSOC);

    if($sqlduplicateuser->rowCount() > 0) {
        echo "Duplication is Prohibited! \n Please Find another Username";
    } else {
        $sqlupdate = "UPDATE tb_municipality SET username = ? WHERE id = ?";
        $db->prepare($sqlupdate)->execute([$username, $id]);
        echo "Username Successfully Updated";
    }
} else if($TypeofUser == "Provincial") {
    $provid = $_SESSION['user_reg'];
    $sqlduplicateuser = $db->prepare("SELECT * FROM tb_provincial WHERE provid != '$provid' AND username = '$username'");
    $sqlduplicateuser->execute();
    $rowuser = $sqlduplicateuser->fetch(PDO::FETCH_ASSOC);

    if($sqlduplicateuser->rowCount() > 0) {
        echo "Duplication is Prohibited! \n Please Find another Username";
    } else {
        $sqlupdate = "UPDATE tb_provincial SET username = ? WHERE provid = ?";
        $db->prepare($sqlupdate)->execute([$username, $provid]);
        echo "Username Successfully Updated";
    }
}