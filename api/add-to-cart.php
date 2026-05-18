<?php
require_once '../config/database.php';

$guest_id = $_SESSION['user_reg'];
$resortid = $_POST['resortid'];
$checkindate = $_POST['checkindate'];
$checkoutdate = $_POST['checkoutdate'];
$resort_room_id = $_POST['resort_room_id'];
$num_adults = $_POST['num_adults'];
$num_kids = $_POST['num_kids'];

$curDT = date("Y-m-d h:i"); 

if(($checkindate <= $curDT) || ($checkoutdate <= $curDT)) {
    echo "The Date and Time you Selected for the Duration has Already Passed, \n Please Correct your Choices and \n Select a Date and Time where it is greater to the Current Date";
} else if($checkoutdate < $checkindate) {
    echo "Plese check your Date! \n Check out Date must be ahead on Check in Date!";
} else {
    $sqladdtocart = "INSERT INTO tb_cart(guest_id, resortid, resort_room_id, checkindate, checkoutdate, num_adults, num_kids)VALUES('$guest_id', '$resortid', '$resort_room_id','$checkindate', '$checkoutdate', '$num_adults', '$num_kids')";
    $statement = $db->prepare($sqladdtocart);
    $statement->execute();
    echo "OK";
}