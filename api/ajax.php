<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch($action) {
    // Authentication endpoints
    case 'login':
        $username = $_POST['username'];
        $password = $_POST['password'];
        $userType = $_POST['user_type'];
        
        if($userType == 'guest') {
            $stmt = $db->prepare("SELECT * FROM tb_guest WHERE Username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if($user && $password == $user['Password']) {
                $_SESSION['user_id'] = $user['guest_id'];
                $_SESSION['user_type'] = 'guest';
                $_SESSION['user_name'] = $user['FirstName'];
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
            }
        } 
        elseif($userType == 'resort') {
            $stmt = $db->prepare("SELECT * FROM tb_resort WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if($user && $password == $user['password']) {
                $_SESSION['user_id'] = $user['resortid'];
                $_SESSION['user_type'] = 'resort';
                $_SESSION['user_name'] = $user['resortname'];
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
            }
        }
        elseif($userType == 'municipal') {
            $stmt = $db->prepare("SELECT * FROM tb_municipality WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if($user && $password == $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'municipal';
                $_SESSION['user_name'] = $user['mun'];
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
            }
        }
        elseif($userType == 'provincial') {
            $stmt = $db->prepare("SELECT * FROM tb_provincial WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if($user && $password == $user['password']) {
                $_SESSION['user_id'] = $user['provid'];
                $_SESSION['user_type'] = 'provincial';
                $_SESSION['user_name'] = $user['username'];
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
            }
        }
        break;
        
    case 'register':
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $check = $db->prepare("SELECT guest_id FROM tb_guest WHERE Username = ?");
        $check->execute([$username]);
        if($check->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
            break;
        }
        
        $checkEmail = $db->prepare("SELECT guest_id FROM tb_guest WHERE Email = ?");
        $checkEmail->execute([$email]);
        if($checkEmail->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            break;
        }
        
        $stmt = $db->prepare("INSERT INTO tb_guest (LastName, FirstName, Address, ContactNo, Username, Password, Email, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'Offline')");
        if($stmt->execute([$lastname, $firstname, $address, $phone, $username, $password, $email])) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }
        break;
        
    case 'check-username':
        $username = $_POST['username'];
        $stmt = $db->prepare("SELECT guest_id FROM tb_guest WHERE Username = ?");
        $stmt->execute([$username]);
        echo json_encode(['exists' => $stmt->rowCount() > 0]);
        break;
        
    case 'change-password':
        session_start();
        $userId = $_SESSION['user_id'];
        $userType = $_SESSION['user_type'];
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        
        if($userType == 'guest') {
            $stmt = $db->prepare("SELECT Password FROM tb_guest WHERE guest_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if($user && $currentPassword == $user['Password']) {
                $update = $db->prepare("UPDATE tb_guest SET Password = ? WHERE guest_id = ?");
                $update->execute([$newPassword, $userId]);
                echo json_encode(['status' => 'success', 'message' => 'Password changed']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Password change not available for this account type']);
        }
        break;
        
    // Booking endpoints
    case 'create-booking':
        session_start();
        $guestId = $_SESSION['user_id'];
        $resortid = $_POST['resortid'];
        $resort_room_id = $_POST['resort_room_id'] ?: null;
        $checkindate = $_POST['checkindate'];
        $checkoutdate = $_POST['checkoutdate'];
        $num_adults = $_POST['num_adults'];
        $num_kids = $_POST['num_kids'];
        $total_amount = $_POST['total_amount'];
        
        $resort = $db->prepare("SELECT adultEntranceFee, kidsEntranceFee FROM tb_resort WHERE resortid = ?");
        $resort->execute([$resortid]);
        $fees = $resort->fetch();
        
        $adult_fee = $num_adults * $fees['adultEntranceFee'];
        $kids_fee = $num_kids * $fees['kidsEntranceFee'];
        
        $cart = $db->prepare("INSERT INTO tb_cart (guest_id, resortid, resort_room_id, checkindate, checkoutdate, num_adults, num_kids, cart_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'Place Order')");
        $cart->execute([$guestId, $resortid, $resort_room_id, $checkindate, $checkoutdate, $num_adults, $num_kids]);
        $cartId = $db->lastInsertId();
        
        $resortStmt = $db->prepare("SELECT adultEntranceFee, kidsEntranceFee FROM tb_resort WHERE resortid = ?");
        $resortStmt->execute([$resortid]);
        $resortData = $resortStmt->fetch();
        
        $checkin_dt = new DateTime($checkindate);
        $checkout_dt = new DateTime($checkoutdate);
        $numDays = max(1, $checkout_dt->diff($checkin_dt)->days);
        
        $room_fee = 0;
        if ($resort_room_id > 0) {
            $roomStmt = $db->prepare("SELECT room_price FROM tb_resort_room WHERE resort_room_id = ?");
            $roomStmt->execute([$resort_room_id]);
            $roomData = $roomStmt->fetch();
            if ($roomData) {
                $room_fee = $roomData['room_price'] * $numDays;
            }
        }
        
        $total_amount = $adult_fee + $kids_fee + $room_fee;
        
        $maxOrderId = $db->query("SELECT MAX(po_id) as max_id FROM tb_placed_order")->fetch();
        $newOrderId = ($maxOrderId['max_id'] ?? 0) + 1;
        
        $columns = "po_id, cart_id, adult_fee, kids_fee, total_fee, payment_method, message, reservation_status";
        $values = "?, ?, ?, ?, ?, 'Cash on Arrival', '', 'Pending'";
        $params = [$newOrderId, $cartId, $adult_fee, $kids_fee, $total_amount];
        
        try {
            $db->query("SELECT room_fee FROM tb_placed_order LIMIT 0");
            $columns = "po_id, cart_id, adult_fee, kids_fee, room_fee, num_days, total_fee, payment_method, message, reservation_status";
            $values = "?, ?, ?, ?, ?, ?, ?, 'Cash on Arrival', '', 'Pending'";
            $params = [$newOrderId, $cartId, $adult_fee, $kids_fee, $room_fee, $numDays, $total_amount];
        } catch (Exception $e) {}
        
        $order = $db->prepare("INSERT INTO tb_placed_order ($columns) VALUES ($values)");
        $order->execute($params);
        
        echo json_encode(['status' => 'success', 'message' => 'Booking created', 'redirect' => '?page=my-bookings']);
        break;
        
    case 'cancel-booking':
        session_start();
        $cartId = $_POST['cart_id'];
        $guestId = $_SESSION['user_id'];
        
        $check = $db->prepare("SELECT cart_id FROM tb_cart WHERE cart_id = ? AND guest_id = ?");
        $check->execute([$cartId, $guestId]);
        
        if($check->rowCount() > 0) {
            $update = $db->prepare("UPDATE tb_cart SET cart_status = 'Cancelled' WHERE cart_id = ?");
            $update->execute([$cartId]);
            $updateOrder = $db->prepare("UPDATE tb_placed_order SET reservation_status = 'Cancelled' WHERE cart_id = ?");
            $updateOrder->execute([$cartId]);
            echo json_encode(['status' => 'success', 'message' => 'Booking cancelled']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
        }
        break;
        
    // Resort management endpoints
    case 'update-reservation-status':
        session_start();
        $cartId = $_POST['cart_id'];
        $status = $_POST['status'];
        $reason = $_POST['reason'] ?? '';
        
        $stmt = $db->prepare("UPDATE tb_placed_order SET reservation_status = ?, reject_reason = ? WHERE cart_id = ?");
        $stmt->execute([$status, $reason, $cartId]);
        echo json_encode(['status' => 'success', 'message' => 'Status updated']);
        break;
        
    case 'add-staff':
        session_start();
        $resortId = $_SESSION['user_id'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $position = $_POST['position'];
        $hireDate = $_POST['hire_date'];
        
        $stmt = $db->prepare("INSERT INTO tb_staff (resortid, first_name, last_name, email, phone, position, hire_date, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
        $stmt->execute([$resortId, $firstName, $lastName, $email, $phone, $position, $hireDate]);
        echo json_encode(['status' => 'success', 'message' => 'Staff added successfully']);
        break;
        
    case 'add-guest':
        session_start();
        $resortId = $_SESSION['user_id'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $contactNo = $_POST['contact_no'];
        $email = $_POST['email'] ?? '';
        $address = $_POST['address'] ?? '';
        $visitDate = $_POST['visit_date'];
        $numAdults = $_POST['num_adults'] ?? 1;
        $numKids = $_POST['num_kids'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        $check = $db->prepare("SELECT guest_id FROM tb_guest WHERE ContactNo = ?");
        $check->execute([$contactNo]);
        
        if($check->rowCount() > 0) {
            $guestId = $check->fetch()['guest_id'];
        } else {
            $username = strtolower($firstName . $lastName);
            $password = 'password123';
            $insert = $db->prepare("INSERT INTO tb_guest (LastName, FirstName, ContactNo, Address, Username, Password, Email, status) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
            $insert->execute([$lastName, $firstName, $contactNo, $address, $username, $password, $email]);
            $guestId = $db->lastInsertId();
        }
        
        $record = $db->prepare("INSERT INTO tb_guest_records (guest_id, resortid, visit_date, num_visits, notes) 
                                 VALUES (?, ?, ?, 1, ?)");
        $record->execute([$guestId, $resortId, $visitDate, $notes]);
        
        echo json_encode(['status' => 'success', 'message' => 'Guest record added']);
        break;
        
    case 'add-task':
        session_start();
        $resortId = $_SESSION['user_id'];
        $staffId = $_POST['staff_id'];
        $taskName = $_POST['task_name'];
        $taskDescription = $_POST['task_description'];
        $dueDate = $_POST['due_date'];
        $priority = $_POST['priority'];
        
        $stmt = $db->prepare("INSERT INTO tb_task_assignment (staff_id, resortid, task_name, task_description, due_date, priority, status) 
                               VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$staffId, $resortId, $taskName, $taskDescription, $dueDate, $priority]);
        echo json_encode(['status' => 'success', 'message' => 'Task assigned']);
        break;
        
    case 'save-room':
        session_start();
        $resortId = $_SESSION['user_id'];
        $roomId = $_POST['room_id'] ?? null;
        $roomName = $_POST['room_name'];
        $roomDescription = $_POST['room_description'] ?? '';
        $capacity = $_POST['capacity'];
        $price = $_POST['price'];
        $status = $_POST['status'];
        
        if($roomId) {
            $stmt = $db->prepare("UPDATE tb_resort_room SET room_name = ?, room_description = ?, room_capacity = ?, room_price = ?, room_status = ? WHERE resort_room_id = ? AND resortid = ?");
            $stmt->execute([$roomName, $roomDescription, $capacity, $price, $status, $roomId, $resortId]);
        } else {
            $stmt = $db->prepare("INSERT INTO tb_resort_room (resortid, room_name, room_description, room_capacity, room_price, room_status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$resortId, $roomName, $roomDescription, $capacity, $price, $status]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Room saved']);
        break;
        
    case 'delete-room':
        session_start();
        $roomId = $_POST['room_id'];
        $resortId = $_SESSION['user_id'];
        $stmt = $db->prepare("DELETE FROM tb_resort_room WHERE resort_room_id = ? AND resortid = ?");
        $stmt->execute([$roomId, $resortId]);
        echo json_encode(['status' => 'success', 'message' => 'Room deleted']);
        break;
        
    case 'get-room':
        $roomId = $_GET['id'];
        $stmt = $db->prepare("SELECT * FROM tb_resort_room WHERE resort_room_id = ?");
        $stmt->execute([$roomId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;
        
    case 'save-amenity':
        session_start();
        $resortId = $_SESSION['user_id'];
        $amenityId = $_POST['amenity_id'] ?? null;
        $amenityName = $_POST['amenity_name'];
        $amenityPrice = $_POST['amenity_price'];
        
        if($amenityId) {
            $stmt = $db->prepare("UPDATE tb_resort_amenities SET amenity_name = ?, amenity_price = ? WHERE amenity_id = ? AND resortid = ?");
            $stmt->execute([$amenityName, $amenityPrice, $amenityId, $resortId]);
        } else {
            $stmt = $db->prepare("INSERT INTO tb_resort_amenities (resortid, amenity_name, amenity_price) VALUES (?, ?, ?)");
            $stmt->execute([$resortId, $amenityName, $amenityPrice]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Amenity saved']);
        break;
        
    case 'delete-amenity':
        session_start();
        $amenityId = $_POST['amenity_id'];
        $resortId = $_SESSION['user_id'];
        $stmt = $db->prepare("DELETE FROM tb_resort_amenities WHERE amenity_id = ? AND resortid = ?");
        $stmt->execute([$amenityId, $resortId]);
        echo json_encode(['status' => 'success', 'message' => 'Amenity deleted']);
        break;
        
    case 'get-amenity':
        $amenityId = $_GET['id'];
        $stmt = $db->prepare("SELECT * FROM tb_resort_amenities WHERE amenity_id = ?");
        $stmt->execute([$amenityId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;
        
    case 'submit-report':
        session_start();
        $resortId = $_SESSION['user_id'];
        $maleDomestic = $_POST['male_domestic'];
        $femaleDomestic = $_POST['female_domestic'];
        $maleForeign = $_POST['male_foreign'];
        $femaleForeign = $_POST['female_foreign'];
        $rdate = $_POST['rdate'];
        $rsales = $_POST['rsales'];
        $rexpenses = $_POST['rexpenses'];
        
        $dcxQuan = $maleDomestic + $femaleDomestic;
        $fcxQuan = $maleForeign + $femaleForeign;
        $totalCustomer = $dcxQuan + $fcxQuan;
        
        $check = $db->prepare("SELECT id FROM tb_report WHERE resortid = ? AND rdate = ?");
        $check->execute([$resortId, $rdate]);
        
        if($check->rowCount() > 0) {
            $stmt = $db->prepare("UPDATE tb_report SET male_domestic = ?, female_domestic = ?, dcx_quan = ?, male_foreign = ?, female_foreign = ?, fcx_quan = ?, total_customer = ?, rsales = ?, rexpenses = ?, rstatus = 'Pending' WHERE resortid = ? AND rdate = ?");
            $stmt->execute([$maleDomestic, $femaleDomestic, $dcxQuan, $maleForeign, $femaleForeign, $fcxQuan, $totalCustomer, $rsales, $rexpenses, $resortId, $rdate]);
        } else {
            $nextId = $db->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM tb_report")->fetch()['next_id'];
            $nextReportId = $db->query("SELECT COALESCE(MAX(reportid), 1000) + 1 AS next_id FROM tb_report")->fetch()['next_id'];
            $stmt = $db->prepare("INSERT INTO tb_report (id, reportid, resortid, male_domestic, female_domestic, dcx_quan, male_foreign, female_foreign, fcx_quan, total_customer, rdate, rsales, rexpenses, rstatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$nextId, $nextReportId, $resortId, $maleDomestic, $femaleDomestic, $dcxQuan, $maleForeign, $femaleForeign, $fcxQuan, $totalCustomer, $rdate, $rsales, $rexpenses]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Report submitted']);
        break;
        
    case 'validate-report':
        session_start();
        $reportId = (int)$_POST['report_id'];
        $action = $_POST['action'];
        $reason = $_POST['reason'] ?? '';
        
        if($action == 'validate') {
            $stmt = $db->prepare("UPDATE tb_report SET rstatus = 'Validated', date_validated = NOW() WHERE id = ? AND rstatus = 'Pending'");
            $stmt->execute([$reportId]);
            echo json_encode(['status' => 'success', 'message' => 'Report validated']);
        } elseif($action == 'reject') {
            $stmt = $db->prepare("UPDATE tb_report SET rstatus = 'Invalid' WHERE id = ? AND rstatus = 'Pending'");
            $stmt->execute([$reportId]);
            
            $report = $db->prepare("SELECT r.*, rs.resortname, rs.mun, rs.district FROM tb_report r JOIN tb_resort rs ON r.resortid = rs.resortid WHERE r.id = ?");
            $report->execute([$reportId]);
            $rep = $report->fetch();
            
            $insert = $db->prepare("INSERT INTO tb_invalid (reportid, resortname, mun, district, remarks) VALUES (?, ?, ?, ?, ?)");
            $insert->execute([$reportId, $rep['resortname'], $rep['mun'], $rep['district'], $reason]);
            echo json_encode(['status' => 'success', 'message' => 'Report rejected']);
        }
        break;
        
    case 'view-report':
        $reportId = $_GET['id'];
        $stmt = $db->prepare("
            SELECT r.*, rs.resortname, rs.resortaddress, rs.mun, rs.district, rs.contact_no
            FROM tb_report r 
            JOIN tb_resort rs ON r.resortid = rs.resortid 
            WHERE r.id = ?
        ");
        $stmt->execute([$reportId]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($report) {
            $remarks = null;
            if($report['rstatus'] == 'Invalid') {
                $invStmt = $db->prepare("SELECT remarks FROM tb_invalid WHERE reportid = ? ORDER BY idate DESC LIMIT 1");
                $invStmt->execute([$reportId]);
                $remarks = $invStmt->fetch(PDO::FETCH_ASSOC);
            }
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Report Details - <?php echo $report['resortname']; ?></title>
                <script src="https://cdn.tailwindcss.com"></script>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
            </head>
            <body class="bg-gray-100 p-8">
                <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 text-white">
                        <h2 class="text-2xl font-bold">Province of Iloilo</h2>
                        <h3 class="text-xl">Resort Monthly Report</h3>
                        <p><?php echo date('F Y', strtotime($report['rdate'])); ?></p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <p class="text-gray-500 text-sm">Resort Name</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($report['resortname']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Municipality</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($report['mun']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">District</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($report['district']); ?></p>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Contact</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($report['contact_no']); ?></p>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mb-4">
                            <h4 class="font-bold text-lg mb-3">Visitor Statistics</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-500 text-sm">Domestic Visitors</p>
                                    <p class="font-semibold"><?php echo number_format($report['dcx_quan']); ?></p>
                                    <p class="text-sm text-gray-500">Male: <?php echo number_format($report['male_domestic']); ?> | Female: <?php echo number_format($report['female_domestic']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Foreign Visitors</p>
                                    <p class="font-semibold"><?php echo number_format($report['fcx_quan']); ?></p>
                                    <p class="text-sm text-gray-500">Male: <?php echo number_format($report['male_foreign']); ?> | Female: <?php echo number_format($report['female_foreign']); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Total Visitors</p>
                                    <p class="font-semibold text-purple-600"><?php echo number_format($report['total_customer']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 mb-4">
                            <h4 class="font-bold text-lg mb-3">Financial Report</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-gray-500 text-sm">Total Sales</p>
                                    <p class="font-semibold text-green-600">₱<?php echo number_format($report['rsales'], 2); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Total Expenses</p>
                                    <p class="font-semibold text-red-600">₱<?php echo number_format($report['rexpenses'], 2); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Net Profit</p>
                                    <p class="font-semibold text-purple-600">₱<?php echo number_format($report['rsales'] - $report['rexpenses'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500 text-sm">Validation Status</p>
                                    <?php if($report['rstatus'] == 'Pending'): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Pending</span>
                                    <?php elseif($report['rstatus'] == 'Validated'): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Validated</span>
                                    <?php elseif($report['rstatus'] == 'Invalid'): ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Invalid</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if($report['rstatus'] == 'Validated' && $report['date_validated']): ?>
                                    <p class="text-gray-500 text-sm">Date Validated</p>
                                    <p class="font-semibold"><?php echo date('M j, Y h:i A', strtotime($report['date_validated'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if($report['rstatus'] == 'Invalid' && $remarks): ?>
                            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-red-700 text-sm font-medium">Rejection Remarks:</p>
                                <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($remarks['remarks']); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex gap-3 justify-end mt-6 pt-4 border-t border-gray-200">
                            <button onclick="window.print()" class="px-4 py-2 bg-purple-600 text-white rounded-lg">Print Report</button>
                            <button onclick="window.close()" class="px-4 py-2 border border-gray-300 rounded-lg">Close</button>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
        }
        exit;
        break;
        
    case 'update-location':
        session_start();
        $resortid = $_POST['resortid'];
        $lat = $_POST['lat'];
        $lon = $_POST['lon'];
        $address = $_POST['address'];
        $name = $_POST['resortname'];
        
        $check = $db->prepare("SELECT location_id FROM tb_location WHERE resortid = ?");
        $check->execute([$resortid]);
        
        if($check->rowCount() > 0) {
            $stmt = $db->prepare("UPDATE tb_location SET lat = ?, lon = ?, address = ? WHERE resortid = ?");
            $stmt->execute([$lat, $lon, $address, $resortid]);
        } else {
            $stmt = $db->prepare("INSERT INTO tb_location (resortid, name, lat, lon, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$resortid, $name, $lat, $lon, $address]);
        }
        
        $updateResort = $db->prepare("UPDATE tb_resort SET isLocated = 1 WHERE resortid = ?");
        $updateResort->execute([$resortid]);
        
        echo json_encode(['status' => 'success', 'message' => 'Location updated']);
        break;
        
    case 'get-resort-flags':
        $resortId = $_GET['id'];
        $stmt = $db->prepare("SELECT resortid, resortname, isFeatured, isTopItem, isBestSeller, isPromoDeals, isOnSale FROM tb_resort WHERE resortid = ?");
        $stmt->execute([$resortId]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        break;
        
    case 'update-resort-flags':
        session_start();
        $resortid = $_POST['resortid'];
        $isFeatured = isset($_POST['isFeatured']) ? 1 : 0;
        $isTopItem = isset($_POST['isTopItem']) ? 1 : 0;
        $isBestSeller = isset($_POST['isBestSeller']) ? 1 : 0;
        $isPromoDeals = isset($_POST['isPromoDeals']) ? 1 : 0;
        $isOnSale = isset($_POST['isOnSale']) ? 1 : 0;
        
        $stmt = $db->prepare("UPDATE tb_resort SET isFeatured = ?, isTopItem = ?, isBestSeller = ?, isPromoDeals = ?, isOnSale = ? WHERE resortid = ?");
        $stmt->execute([$isFeatured, $isTopItem, $isBestSeller, $isPromoDeals, $isOnSale, $resortid]);
        echo json_encode(['status' => 'success', 'message' => 'Resort flags updated']);
        break;
        
    case 'export-reports':
        $status = $_GET['status'] ?? 'all';
        $district = $_GET['district'] ?? 'all';
        
        $sql = "
            SELECT r.rdate, rs.resortname, rs.mun, rs.district, 
                   r.dcx_quan as domestic_visitors, r.fcx_quan as foreign_visitors, 
                   r.total_customer, r.rsales as sales, r.rexpenses as expenses,
                   (r.rsales - r.rexpenses) as profit, r.rstatus
            FROM tb_report r 
            JOIN tb_resort rs ON r.resortid = rs.resortid 
            WHERE 1=1
        ";
        $params = [];
        
        if($status != 'all') {
            $sql .= " AND r.rstatus = ?";
            $params[] = $status;
        }
        if($district != 'all') {
            $sql .= " AND rs.district = ?";
            $params[] = $district;
        }
        $sql .= " ORDER BY r.rdate DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reports_export_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'Report Date', 'Resort Name', 'Municipality', 'District',
            'Domestic Visitors', 'Foreign Visitors', 'Total Customers',
            'Sales (₱)', 'Expenses (₱)', 'Profit (₱)', 'Status'
        ]);
        
        foreach($reports as $report) {
            fputcsv($output, [
                date('F Y', strtotime($report['rdate'])),
                $report['resortname'],
                $report['mun'],
                $report['district'],
                $report['domestic_visitors'],
                $report['foreign_visitors'],
                $report['total_customer'],
                number_format($report['sales'], 2),
                number_format($report['expenses'], 2),
                number_format($report['profit'], 2),
                $report['rstatus']
            ]);
        }
        fclose($output);
        exit;
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>