<?php
$guestId = $_SESSION['user_reg'];
$guest = $conn->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
$guest->execute([$guestId]);
$guestData = $guest->fetch();

$upcomingBookings = $conn->prepare("
    SELECT c.*, po.reservation_status, r.resortname, r.adultEntranceFee, r.kidsEntranceFee,
           po.total_fee, po.payment_method
    FROM tb_cart c 
    JOIN tb_resort r ON c.resortid = r.resortid 
    LEFT JOIN tb_placed_order po ON c.cart_id = po.cart_id
    WHERE c.guest_id = ? AND c.checkindate >= CURDATE() AND c.cart_status = 'Place Order'
    ORDER BY c.checkindate ASC LIMIT 5
");
$upcomingBookings->execute([$guestId]);

$pastBookings = $conn->prepare("
    SELECT COUNT(*) as count FROM tb_cart 
    WHERE guest_id = ? AND checkindate < CURDATE() AND cart_status = 'Place Order'
");
$pastBookings->execute([$guestId]);
$pastCount = $pastBookings->fetch()['count'];

$totalSpent = $conn->prepare("
    SELECT COALESCE(SUM(po.total_fee), 0) as total 
    FROM tb_placed_order po 
    JOIN tb_cart c ON po.cart_id = c.cart_id 
    WHERE c.guest_id = ? AND po.reservation_status IN ('Completed', 'Reviewed')
");
$totalSpent->execute([$guestId]);
$totalSpentAmount = $totalSpent->fetch()['total'] ?? 0;

$pendingCount = $conn->prepare("
    SELECT COUNT(*) as count FROM tb_placed_order po 
    JOIN tb_cart c ON po.cart_id = c.cart_id 
    WHERE c.guest_id = ? AND po.reservation_status = 'Pending'
");
$pendingCount->execute([$guestId]);
$pendingNum = $pendingCount->fetch()['count'];

$recommended = $conn->query("SELECT * FROM tb_resort WHERE isFeatured = 1 AND isLocated = 1 LIMIT 4")->fetchAll();
?>

<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center shadow-lg">
                <span class="text-white text-2xl font-bold"><?php echo strtoupper(substr($guestData['FirstName'], 0, 1)); ?></span>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($guestData['FirstName']); ?>!</h1>
                <p class="text-gray-500">Ready for your next adventure?</p>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="?page=guest-gallery" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition flex items-center gap-2">
                <i class="bi bi-images"></i> Gallery
            </a>
            <a href="?page=resorts" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-medium hover:shadow-lg transition flex items-center gap-2">
                <i class="bi bi-compass"></i> Explore Resorts
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <!-- Upcoming -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-calendar-check text-blue-500 text-xl"></i>
                </div>
                <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-full">Trips</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-800"><?php echo $upcomingBookings->rowCount(); ?></h3>
            <p class="text-gray-500 text-sm">Upcoming Bookings</p>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-hourglass-split text-amber-500 text-xl"></i>
                </div>
                <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-full">Pending</span>
            </div>
            <h3 class="text-3xl font-bold text-amber-600"><?php echo $pendingNum; ?></h3>
            <p class="text-gray-500 text-sm">Awaiting Confirmation</p>
        </div>

        <!-- Total Spent -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <i class="bi bi-cash-stack text-emerald-500 text-xl"></i>
                </div>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Spent</span>
            </div>
            <h3 class="text-3xl font-bold text-emerald-600">₱<?php echo number_format($totalSpentAmount, 0); ?></h3>
            <p class="text-gray-500 text-sm">Total Spent</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Upcoming Bookings -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Upcoming Bookings</h2>
                    <p class="text-gray-500 text-sm">Your upcoming resort visits</p>
                </div>
                <a href="?page=guest-bookings" class="text-sm text-purple-600 font-medium hover:text-purple-700 flex items-center gap-1">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if($upcomingBookings->rowCount() > 0): ?>
                    <?php while($booking = $upcomingBookings->fetch()): ?>
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-xl flex items-center justify-center text-white font-bold">
                                    <?php echo strtoupper(substr($booking['resortname'], 0, 2)); ?>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($booking['resortname']); ?></h4>
                                    <div class="flex items-center gap-3 text-sm text-gray-500 mt-1">
                                        <span class="flex items-center gap-1"><i class="bi bi-calendar"></i> <?php echo date('M j', strtotime($booking['checkindate'])); ?> - <?php echo date('M j, Y', strtotime($booking['checkoutdate'])); ?></span>
                                        <span class="flex items-center gap-1"><i class="bi bi-people"></i> <?php echo ($booking['num_adults'] ?? 0) + ($booking['num_kids'] ?? 0); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <?php 
                                $status = $booking['reservation_status'] ?? 'Pending';
                                $statusConfig = [
                                    'Pending' => ['bg' => 'bg-amber-100 text-amber-700', 'icon' => 'bi-hourglass-split'],
                                    'Approved' => ['bg' => 'bg-blue-100 text-blue-700', 'icon' => 'bi-check-circle'],
                                    'PaymentApproval' => ['bg' => 'bg-purple-100 text-purple-700', 'icon' => 'bi-credit-card'],
                                    'Completed' => ['bg' => 'bg-emerald-100 text-emerald-700', 'icon' => 'bi-check-circle-fill'],
                                    'Rejected' => ['bg' => 'bg-red-100 text-red-700', 'icon' => 'bi-x-circle'],
                                ];
                                $config = $statusConfig[$status] ?? ['bg' => 'bg-gray-100 text-gray-700', 'icon' => 'bi-circle'];
                                ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?php echo $config['bg']; ?>">
                                    <i class="bi <?php echo $config['icon']; ?>"></i> <?php echo $status; ?>
                                </span>
                                <span class="font-semibold text-gray-800">₱<?php echo number_format($booking['total_fee'] ?? 0, 2); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="bi bi-calendar-x text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No upcoming bookings</p>
                        <p class="text-gray-400 text-sm mb-4">Plan your next getaway!</p>
                        <a href="?page=resorts" class="inline-flex items-center gap-1 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm hover:bg-purple-700 transition">
                            <i class="bi bi-compass"></i> Browse Resorts
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="?page=resorts" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-compass text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Explore Resorts</h4>
                        <p class="text-xs text-gray-500">Find new places</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=guest-bookings" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-calendar-check text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">My Bookings</h4>
                        <p class="text-xs text-gray-500"><?php echo $upcomingBookings->rowCount(); ?> upcoming</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
                <a href="?page=profile" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition group">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <i class="bi bi-person-gear text-emerald-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">Profile</h4>
                        <p class="text-xs text-gray-500">Manage your info</p>
                    </div>
                    <i class="bi bi-chevron-right text-gray-400"></i>
                </a>
            </div>

            <!-- Past Visits Summary -->
            <div class="mt-6 pt-6 border-t border-gray-100">
                <h3 class="text-sm font-semibold text-gray-500 mb-3">TRAVEL STATS</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-calendar-check text-purple-500"></i>
                            <span class="text-sm text-gray-600">Upcoming</span>
                        </div>
                        <span class="font-semibold text-gray-800"><?php echo $upcomingBookings->rowCount(); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-clock-history text-gray-500"></i>
                            <span class="text-sm text-gray-600">Past Visits</span>
                        </div>
                        <span class="font-semibold text-gray-800"><?php echo $pastCount; ?></span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-cash-stack text-emerald-500"></i>
                            <span class="text-sm text-gray-600">Total Spent</span>
                        </div>
                        <span class="font-semibold text-gray-800">₱<?php echo number_format($totalSpentAmount, 0); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommended Resorts -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Recommended for You</h2>
                <p class="text-gray-500 text-sm">Popular resorts to explore</p>
            </div>
            <a href="?page=resorts" class="text-sm text-purple-600 font-medium hover:text-purple-700 flex items-center gap-1">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach($recommended as $rec): ?>
                <div class="bg-gray-50 rounded-xl overflow-hidden hover:shadow-md transition group">
                    <div class="h-32 bg-gradient-to-r from-purple-500 to-emerald-500 flex items-center justify-center">
                        <i class="bi bi-water-wave text-white text-3xl"></i>
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($rec['resortname']); ?></h4>
                        <p class="text-gray-500 text-sm mb-3"><?php echo htmlspecialchars($rec['mun']); ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-purple-600 font-bold">₱<?php echo number_format($rec['adultEntranceFee'], 0); ?></span>
                            <button onclick="bookNow(<?php echo $rec['resortid']; ?>)" class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 transition">
                                Book
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function bookNow(resortId) {
    window.location.href = '?page=booking&id=' + resortId;
}
</script>