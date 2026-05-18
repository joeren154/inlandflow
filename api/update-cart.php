<?php
require_once '../config/database.php';

$checkindate = $_POST['checkindate'];
$checkoutdate = $_POST['checkoutdate'];

$curDT = date("Y-m-d h:i"); 

if(($checkindate <= $curDT) || ($checkoutdate <= $curDT)) {
    echo "The Date and Time you Selected for the Duration has Already Passed, \n Please Correct your Choices and \n Select a Date and Time where it is greater to the Current Date";
} else if($checkoutdate < $checkindate) {
    echo "Plese check your Date! \n Check out Date must be ahead on Check in Date!";
} else {
    $updateCart = $db->prepare("UPDATE tb_cart SET resort_room_id = ?, checkindate = ?, checkoutdate = ?, num_adults = ?, num_kids = ? WHERE cart_id = ?");
    $updateCart->execute([$_POST['resort_room_id'], $checkindate, $checkoutdate, $_POST['num_adults'], $_POST['num_kids'], $_POST['cart_id']]);
    echo "OK";
}