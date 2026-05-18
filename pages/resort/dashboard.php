<?php
$resortId = $_SESSION['user_reg'];

$resort = $conn->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
$resort->execute([$resortId]);
$resortData = $resort->fetch();

if (!$resortData) {
    echo '<script>window.location.href = "$1";</script>';
    exit;
}

$totalBookings = $conn->prepare("
    SELECT COUNT(*) as count, SUM(COALESCE(num_adults, 0) + COALESCE(num_kids, 0)) as guests 
    FROM tb_cart WHERE resortid = ? AND cart_status = 'Place Order'
");
$totalBookings->execute([$resortId]);
$bookingStats = $totalBookings->fetch();

$pendingReservations = $conn->prepare("
    SELECT COUNT(*) as count FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id 
    WHERE c.resortid = ? AND po.reservation_status = 'Pending'
");
$pendingReservations->execute([$resortId]);
$pendingCount = $pendingReservations->fetch()['count'];

$approvedReservations = $conn->prepare("
    SELECT COUNT(*) as count FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id 
    WHERE c.resortid = ? AND po.reservation_status = 'Approved'
");
$approvedReservations->execute([$resortId]);
$approvedCount = $approvedReservations->fetch()['count'];

$completedReservations = $conn->prepare("
    SELECT COUNT(*) as count FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id 
    WHERE c.resortid = ? AND po.reservation_status = 'Completed'
");
$completedReservations->execute([$resortId]);
$completedCount = $completedReservations->fetch()['count'];

$totalRevenue = $conn->prepare("
    SELECT COALESCE(SUM(po.total_fee), 0) as total 
    FROM tb_placed_order po 
    JOIN tb_cart c ON po.cart_id = c.cart_id 
    WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed')
");
$totalRevenue->execute([$resortId]);
$revenueTotal = $totalRevenue->fetch()['total'] ?? 0;

$recentBookings = $conn->prepare("
    SELECT c.*, po.reservation_status, po.payment_method, po.total_fee,
           g.FirstName, g.LastName, g.ContactNo
    FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id 
    JOIN tb_guest g ON c.guest_id = g.guest_id 
    WHERE c.resortid = ? 
    ORDER BY c.cart_id DESC LIMIT 5
");
$recentBookings->execute([$resortId]);

$roomsCount = $conn->prepare("SELECT COUNT(*) as count FROM tb_resort_room WHERE resortid = ?");
$roomsCount->execute([$resortId]);
$roomsNum = $roomsCount->fetch()['count'];
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-2xl flex items-center justify-center shadow-lg">
                <i class="bi bi-water-wave text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($resortData['resortname']); ?></h1>
                <p class="text-gray-500 flex items-center gap-1">
                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($resortData['mun']); ?>, <?php echo htmlspecialchars($resortData['district']); ?>
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="?page=resort-gallery" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center gap-2">
                <i class="bi bi-images"></i> Gallery
            </a>
            <a href="?page=resort-amenities" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center gap-2">
                <i class="bi bi-gear"></i> Amenities
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Bookings -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-calendar-check text-blue-500 text-xl"></i>
                </div>
                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Total</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-800"><?php echo $bookingStats['count'] ?? 0; ?></h3>
            <p class="text-gray-500 text-sm">Total Bookings</p>
            <div class="mt-2 flex items-center gap-1 text-xs text-gray-400">
                <i class="bi bi-people"></i> <?php echo $bookingStats['guests'] ?? 0; ?> guests
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-hourglass-split text-amber-500 text-xl"></i>
                </div>
                <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Pending</span>
            </div>
            <h3 class="text-3xl font-bold text-amber-600"><?php echo $pendingCount; ?></h3>
            <p class="text-gray-500 text-sm">Pending Reservations</p>
            <div class="mt-2 flex items-center gap-1 text-xs text-amber-500">
                <i class="bi bi-exclamation-circle"></i> Awaiting approval
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-check-circle text-purple-500 text-xl"></i>
                </div>
                <span class="text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Approved</span>
            </div>
            <h3 class="text-3xl font-bold text-purple-600"><?php echo $approvedCount; ?></h3>
            <p class="text-gray-500 text-sm">Approved Bookings</p>
        </div>

        <!-- Revenue -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-cash-stack text-emerald-500 text-xl"></i>
                </div>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Income</span>
            </div>
            <h3 class="text-3xl font-bold text-emerald-600">₱<?php echo number_format($revenueTotal, 0); ?></h3>
            <p class="text-gray-500 text-sm">Total Revenue</p>
            <div class="mt-2 flex items-center gap-1 text-xs text-emerald-500">
                <i class="bi bi-check-circle"></i> Completed bookings
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Recent Reservations -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Recent Reservations</h2>
                    <p class="text-gray-500 text-sm">Latest booking requests</p>
                </div>
                <a href="?page=resort-reservations" class="text-sm text-purple-600 font-medium hover:text-purple-700 flex items-center gap-1">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Guest</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Check-in</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Guests</th>
                            <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Status</th>
                            <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if($recentBookings->rowCount() > 0): ?>
                            <?php while($booking = $recentBookings->fetch()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-full flex items-center justify-center text-white font-semibold">
                                            <?php echo strtoupper(substr($booking['FirstName'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800"><?php echo htmlspecialchars($booking['FirstName'] . ' ' . $booking['LastName']); ?></p>
                                            <p class="text-xs text-gray-400"><?php echo htmlspecialchars($booking['ContactNo']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-800"><?php echo date('M j, Y', strtotime($booking['checkindate'])); ?></p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-600"><?php echo ($booking['num_adults'] ?? 0) + ($booking['num_kids'] ?? 0); ?> guests</p>
                                </td>
                                <td class="px-5 py-4">
                                    <?php 
                                    $statusConfig = [
                                        'Pending' => ['bg' => 'bg-amber-100 text-amber-700', 'icon' => 'bi-hourglass-split'],
                                        'Approved' => ['bg' => 'bg-blue-100 text-blue-700', 'icon' => 'bi-check-circle'],
                                        'Completed' => ['bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'bi-check-circle-fill'],
                                        'Rejected' => ['bg' => 'bg-red-100 text-red-700', 'icon' => 'bi-x-circle'],
                                    ];
                                    $status = $booking['reservation_status'];
                                    $config = $statusConfig[$status] ?? ['bg' => 'bg-gray-100 text-gray-700', 'icon' => 'bi-circle'];
                                    ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?php echo $config['bg']; ?>">
                                        <i class="bi <?php echo $config['icon']; ?>"></i> <?php echo $status; ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <p class="font-semibold text-gray-800">₱<?php echo number_format($booking['total_fee'], 2); ?></p>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="bi bi-calendar-x text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">No reservations yet</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="?page=resort-reservations" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-calendar-check text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Reservations</h4>
                        <p class="text-xs text-gray-500"><?php echo $pendingCount; ?> pending</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=resort-rooms" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-door-open text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Rooms</h4>
                        <p class="text-xs text-gray-500"><?php echo $roomsNum; ?> rooms</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=resort-reports" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-file-text text-emerald-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Reports</h4>
                        <p class="text-xs text-gray-500">Submit reports</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <!-- <a href="?page=resort-profile" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-person-gear text-amber-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Settings</h4>
                        <p class="text-xs text-gray-500">Manage profile</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a> -->
            </div>
        </div>
    </div>
</div>

<script>
setInterval(function() {
    fetch('api/heartbeat.php');
}, 60000);
</script>