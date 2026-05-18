<?php
$resortId = $_SESSION['user_reg'];
$period = $_GET['period'] ?? 'month';

// Helper functions
function getBookingAnalytics($conn, $resortId, $period = 'month') {
    $sql = "SELECT DATE(checkindate) as date, COUNT(*) as bookings, SUM(num_adults + num_kids) as guests 
            FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id
            WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed')";
    if($period == 'month') {
        $sql .= " AND MONTH(c.checkindate) = MONTH(CURRENT_DATE())";
    } elseif($period == 'year') {
        $sql .= " AND YEAR(c.checkindate) = YEAR(CURRENT_DATE())";
    }
    $sql .= " GROUP BY DATE(c.checkindate)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$resortId]);
    return $stmt->fetchAll();
}

function getRevenueAnalytics($conn, $resortId, $period = 'month') {
    $sql = "SELECT DATE(c.checkindate) as date, SUM(po.total_fee) as revenue 
            FROM tb_placed_order po 
            JOIN tb_cart c ON po.cart_id = c.cart_id 
            WHERE po.reservation_status IN ('Completed', 'Reviewed') AND c.resortid = ?";
    if($period == 'month') {
        $sql .= " AND MONTH(c.checkindate) = MONTH(CURRENT_DATE())";
    } elseif($period == 'year') {
        $sql .= " AND YEAR(c.checkindate) = YEAR(CURRENT_DATE())";
    }
    $sql .= " GROUP BY DATE(c.checkindate)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$resortId]);
    return $stmt->fetchAll();
}

// Get analytics data
$bookingAnalytics = getBookingAnalytics($conn, $resortId, $period);
$revenueAnalytics = getRevenueAnalytics($conn, $resortId, $period);

// Get overall stats
$completedStatuses = ['Completed', 'Reviewed'];

$totalBookings = $conn->prepare("SELECT COUNT(*) as count FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed')");
$totalBookings->execute([$resortId]);
$totalBookingsCount = $totalBookings->fetch()['count'] ?? 0;

$totalRevenue = $conn->prepare("SELECT SUM(po.total_fee) as total FROM tb_placed_order po JOIN tb_cart c ON po.cart_id = c.cart_id WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed')");
$totalRevenue->execute([$resortId]);
$totalRevenueAmount = $totalRevenue->fetch()['total'] ?? 0;

$totalGuests = $conn->prepare("SELECT SUM(c.num_adults + c.num_kids) as guests FROM tb_cart c JOIN tb_placed_order po ON c.cart_id = po.cart_id WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed')");
$totalGuests->execute([$resortId]);
$totalGuestsCount = $totalGuests->fetch()['guests'] ?? 0;

// Get room utilization
$roomUtilization = $conn->prepare("
    SELECT r.room_name, 
           (SELECT COUNT(*) FROM tb_cart c2 JOIN tb_placed_order po2 ON c2.cart_id = po2.cart_id WHERE c2.resort_room_id = r.resort_room_id AND po2.reservation_status IN ('Completed', 'Reviewed')) as bookings,
           (SELECT SUM(c2.num_adults + c2.num_kids) FROM tb_cart c2 JOIN tb_placed_order po2 ON c2.cart_id = po2.cart_id WHERE c2.resort_room_id = r.resort_room_id AND po2.reservation_status IN ('Completed', 'Reviewed')) as total_guests
    FROM tb_resort_room r
    WHERE r.resortid = ?
");
$roomUtilization->execute([$resortId]);

// Get all stats for chart calculations
$maxBookings = $conn->prepare("SELECT MAX(daily_bookings) as max FROM (
    SELECT DATE(c.checkindate) as date, COUNT(*) as daily_bookings FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id 
    WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed') GROUP BY DATE(c.checkindate)
) as daily");
$maxBookings->execute([$resortId]);
$maxBookingCount = $maxBookings->fetch()['max'] ?? 1;

$maxRevenue = $conn->prepare("SELECT MAX(daily_revenue) as max FROM (
    SELECT DATE(c.checkindate) as date, SUM(po.total_fee) as daily_revenue FROM tb_cart c 
    JOIN tb_placed_order po ON c.cart_id = po.cart_id 
    WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed') GROUP BY DATE(c.checkindate)
) as daily");
$maxRevenue->execute([$resortId]);
$maxRevenueAmount = $maxRevenue->fetch()['max'] ?? 1;

// Get recent bookings
$recentBookings = $conn->prepare("
    SELECT c.*, po.total_fee, po.reservation_status, g.FirstName, g.LastName
    FROM tb_cart c
    JOIN tb_placed_order po ON c.cart_id = po.cart_id
    JOIN tb_guest g ON c.guest_id = g.guest_id
    WHERE c.resortid = ? AND po.reservation_status IN ('Completed', 'Reviewed')
    ORDER BY c.checkindate DESC
    LIMIT 10
");
$recentBookings->execute([$resortId]);
?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-emerald-50 pt-20">
    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-purple-600 to-emerald-500 bg-clip-text text-transparent">
                    Analytics Dashboard
                </h1>
                <p class="text-slate-600 mt-2">Track your resort performance</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <button onclick="location.href='?page=resort-staff'" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl flex items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Staff
                </button>
                <select id="periodSelect" onchange="changePeriod(this.value)" class="px-4 py-2 border border-gray-200 rounded-xl">
                    <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="year" <?php echo $period == 'year' ? 'selected' : ''; ?>>This Year</option>
                </select>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-calendar-check text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Bookings</p>
                        <p class="text-2xl font-bold text-slate-800"><?php echo number_format($totalBookingsCount); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-currency-php text-emerald-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Revenue</p>
                        <p class="text-2xl font-bold text-slate-800">₱<?php echo number_format($totalRevenueAmount); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-people text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Guests</p>
                        <p class="text-2xl font-bold text-slate-800"><?php echo number_format($totalGuestsCount); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-graph-up text-amber-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Avg per Booking</p>
                        <p class="text-2xl font-bold text-slate-800">₱<?php echo $totalBookingsCount > 0 ? number_format($totalRevenueAmount / $totalBookingsCount) : 0; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Bookings Over Time</h3>
                <div class="h-64 flex items-center justify-center">
                    <?php if(count($bookingAnalytics) > 0): ?>
                    <div class="w-full space-y-2">
                        <?php foreach($bookingAnalytics as $booking): 
                            $width = $maxBookingCount > 0 ? ($booking['bookings'] / $maxBookingCount) * 100 : 0;
                        ?>
                        <div class="flex items-center gap-3">
                            <span class="w-20 text-xs text-slate-500"><?php echo date('M j', strtotime($booking['date'])); ?></span>
                            <div class="flex-1 bg-slate-100 rounded-full h-5 relative">
                                <div class="bg-purple-500 h-5 rounded-full absolute left-0" style="width: <?php echo $width; ?>%"></div>
                            </div>
                            <span class="text-xs font-medium w-8 text-right"><?php echo $booking['bookings']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-slate-400">No booking data available</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Revenue Over Time</h3>
                <div class="h-64 flex items-center justify-center">
                    <?php if(count($revenueAnalytics) > 0): ?>
                    <div class="w-full space-y-2">
                        <?php foreach($revenueAnalytics as $revenue): 
                            $width = $maxRevenueAmount > 0 ? ($revenue['revenue'] / $maxRevenueAmount) * 100 : 0;
                        ?>
                        <div class="flex items-center gap-3">
                            <span class="w-20 text-xs text-slate-500"><?php echo date('M j', strtotime($revenue['date'])); ?></span>
                            <div class="flex-1 bg-slate-100 rounded-full h-5 relative">
                                <div class="bg-emerald-500 h-5 rounded-full absolute left-0" style="width: <?php echo $width; ?>%"></div>
                            </div>
                            <span class="text-xs font-medium w-16 text-right">₱<?php echo number_format($revenue['revenue']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-slate-400">No revenue data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Room Utilization -->
        <div class="bg-white rounded-2xl p-6 shadow-lg mb-8">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Room Utilization</h3>
            <div class="space-y-4">
                <?php while($room = $roomUtilization->fetch()): ?>
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="font-medium"><?php echo htmlspecialchars($room['room_name']); ?></span>
                        <span class="text-slate-600"><?php echo $room['bookings']; ?> bookings</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <?php $percentage = min(100, ($room['bookings'] / max(1, $totalBookingsCount)) * 100); ?>
                        <div class="bg-gradient-to-r from-purple-500 to-emerald-500 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if($roomUtilization->rowCount() == 0): ?>
                <p class="text-slate-400 text-center py-4">No room data available</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Bookings -->
        <div class="bg-white rounded-2xl p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Recent Bookings</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Guest</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Check-in</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Guests</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php while($booking = $recentBookings->fetch()): ?>
                        <tr>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($booking['FirstName'] . ' ' . $booking['LastName']); ?></td>
                            <td class="px-4 py-3"><?php echo date('M j, Y', strtotime($booking['checkindate'])); ?></td>
                            <td class="px-4 py-3"><?php echo $booking['num_adults'] + $booking['num_kids']; ?></td>
                            <td class="px-4 py-3">₱<?php echo number_format($booking['total_fee']); ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                    if($booking['reservation_status'] == 'Completed') echo 'bg-green-100 text-green-700';
                                    elseif($booking['reservation_status'] == 'Approved') echo 'bg-blue-100 text-blue-700';
                                    elseif($booking['reservation_status'] == 'Pending') echo 'bg-yellow-100 text-yellow-700';
                                    else echo 'bg-red-100 text-red-700';
                                ?>"><?php echo $booking['reservation_status']; ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if($recentBookings->rowCount() == 0): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">No recent bookings</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function changePeriod(period) {
    window.location.href = '?page=resort-analytics&period=' + period;
}
</script>