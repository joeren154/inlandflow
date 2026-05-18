<?php 
session_start();
ob_start();
include_once 'config/database.php';
$conn = $db; // Alias for compatibility with included pages

// Role-based page routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$type = isset($_SESSION['type_of_user']) ? $_SESSION['type_of_user'] : null;
$userId = isset($_SESSION['user_reg']) ? $_SESSION['user_reg'] : null;

// Get user data based on role
$userData = null;
if($type == "Guest" && $userId) {
    $stmt = $db->prepare("SELECT * FROM tb_guest WHERE guest_id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
} elseif($type == "Resort" && $userId) {
    $stmt = $db->prepare("SELECT * FROM tb_resort WHERE resortid = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
} elseif($type == "Municipal" && $userId) {
    $stmt = $db->prepare("SELECT * FROM tb_municipality WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
} elseif($type == "Provincial" && $userId) {
    $stmt = $db->prepare("SELECT * FROM tb_provincial WHERE provid = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch();
}

// Get featured resorts
$featuredResorts = [];
$stmt = $db->prepare("SELECT * FROM tb_resort WHERE isFeatured = 1 AND isLocated = 1 LIMIT 8");
$stmt->execute();
$featuredResorts = $stmt->fetchAll();

// Get statistics
$totalResorts = $db->query("SELECT COUNT(*) as count FROM tb_resort WHERE isLocated = 1")->fetch()['count'];
$totalGuests = $db->query("SELECT COUNT(*) as count FROM tb_guest")->fetch()['count'];
$totalBookings = $db->query("SELECT COUNT(*) as count FROM tb_cart WHERE cart_status = 'Place Order'")->fetch()['count'];
$totalRevenue = $db->query("SELECT SUM(total_fee) as total FROM tb_placed_order WHERE reservation_status = 'Completed'")->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InlandFlow - Premium Inland Resort Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f3ff 0%, #ecfdf5 100%);
        }
        
        /* Custom Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Navbar Styles */
        .navbar {
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            background: rgba(17, 24, 39, 0.98) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .nav-link {
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: #c084fc !important;
            transform: translateY(-2px);
        }
        
        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #10b981, #059669);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        
        /* Card Styles */
        .resort-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .resort-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .resort-card:hover .resort-image {
            transform: scale(1.1);
        }
        
        .resort-image {
            transition: transform 0.5s ease;
        }
        
        /* Stat Card Styles */
        .stat-card {
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #8b5cf6, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }
        
        /* Dropdown Styles */
        .dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            transform: translateY(-10px);
        }
        
        .group:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        /* Toast Animation */
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .toast-notification {
            animation: slideInRight 0.3s ease-out;
        }
        
        /* Loading Spinner */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border-radius: 4px;
        }
        
        /* Mobile Menu */
        @media (max-width: 768px) {
            .mobile-menu-open {
                max-height: 300px;
                opacity: 1;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar fixed top-0 w-full bg-gray-900 shadow-lg z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            <!-- Logo -->
            <a href="?page=home" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg transition-transform group-hover:scale-110">
                    <i class="bi bi-water-wave text-white text-xl"></i>
                </div>
                <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-emerald-400 bg-clip-text text-transparent">
                    InlandFlow
                </span>
            </a>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-1">
                <?php if(!$type): ?>
                    <a href="?page=home" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">Home</a>
                    <a href="?page=resorts" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">Resorts</a>
                    <a href="?page=about" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">About</a>
                    <a href="?page=contact" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">Contact</a>
                    <!-- <a href="?page=cart" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition relative">
                        <i class="bi bi-cart3 me-1"></i>Cart
                        <?php if(!empty($_SESSION['guest_cart']) && count($_SESSION['guest_cart']) > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-purple-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo count($_SESSION['guest_cart']); ?></span>
                        <?php endif; ?>
                    </a> -->
                    <a href="login/index.php" class="ml-4 px-5 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full hover:shadow-lg transition transform hover:scale-105">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                <?php else: ?>
                    <a href="?page=dashboard" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                    <?php if($type == "Guest"): ?>
                    <a href="?page=guest-resorts" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-compass me-1"></i>Browse
                        </a>
                        <a href="?page=guest-cart" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-cart me-1"></i>Bookings
                        </a>
                        <a href="?page=guest-bookings" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-calendar-check me-1"></i>My Bookings
                        </a>
                    <?php elseif($type == "Resort"): ?>
                        <a href="?page=resort-reservations" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-clipboard-list me-1"></i>Reservations
                        </a>
                        <a href="?page=resort-rooms" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-door-open me-1"></i>Rooms
                        </a>
                        <a href="?page=resort-staff" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-people me-1"></i>Staff
                        </a>
                        <a href="?page=resort-tasks" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-list-task me-1"></i>Tasks
                        </a>
                        <a href="?page=resort-schedule" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-calendar-range me-1"></i>Schedule
                        </a>
                        <a href="?page=resort-analytics" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-graph-up me-1"></i>Analytics
                        </a>
                        <a href="?page=resort-reports" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-file-text me-1"></i>Reports
                        </a>
                    <?php elseif($type == "Municipal"): ?>
                        <a href="?page=municipal-reports" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-file-text me-1"></i>Reports
                        </a>
                    <?php elseif($type == "Provincial"): ?>
                        <a href="?page=provincial-resorts" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-building me-1"></i>Resorts
                        </a>
                    <?php endif; ?>
                    
                    <!-- User Dropdown -->
                    <div class="relative group ml-2">
                        <button class="flex items-center gap-2 px-3 py-2 bg-gray-800 rounded-full hover:bg-gray-700 transition">
                            <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-full flex items-center justify-center">
                                <i class="bi bi-person-fill text-white text-sm"></i>
                            </div>
                            <span class="text-gray-300 text-sm"><?php echo htmlspecialchars($userData['username'] ?? $userData['Username'] ?? 'User'); ?></span>
                            <i class="bi bi-chevron-down text-gray-400 text-xs"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 w-56 bg-white rounded-xl shadow-xl py-2 mt-2 group-hover:opacity-100 group-hover:visible z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800"><?php echo ucfirst($type); ?> Account</p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($userData['username'] ?? $userData['Username'] ?? ''); ?></p>
                            </div>
                            <a href="?page=profile" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
                                <i class="bi bi-person-circle"></i>
                                <span>My Profile</span>
                            </a>
                            <hr class="my-1">
                            <a href="login/logout.php" class="flex items-center gap-3 px-4 py-2 text-red-600 hover:bg-red-50 transition">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu Button -->
            <button id="mobileMenuBtn" class="md:hidden text-gray-300 text-2xl">
                <i class="bi bi-list"></i>
            </button>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden pb-4">
            <?php if(!$type): ?>
                <a href="?page=home" class="block py-2 text-gray-300 hover:text-purple-400 transition">Home</a>
                <a href="?page=resorts" class="block py-2 text-gray-300 hover:text-purple-400 transition">Resorts</a>
                <a href="?page=about" class="block py-2 text-gray-300 hover:text-purple-400 transition">About</a>
                <a href="?page=contact" class="block py-2 text-gray-300 hover:text-purple-400 transition">Contact</a>
                <a href="login/index.php" class="block mt-2 px-4 py-2 bg-purple-600 text-white rounded-lg text-center">Login</a>
            <?php else: ?>
                <a href="?page=dashboard" class="block py-2 text-gray-300">Dashboard</a>
                <a href="?page=profile" class="block py-2 text-gray-300">Profile</a>
                <a href="login/logout.php" class="block mt-2 px-4 py-2 bg-red-600 text-white rounded-lg text-center">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="pt-16">
    <?php if(!$type || $page == 'home'): ?>
    <!-- Hero Section -->
    <section class="hero-section min-h-[80vh] flex items-center justify-center text-white text-center relative">
        <div class="container mx-auto px-4 relative z-10">
            <div class="animate-fadeInUp">
                <h1 class="text-5xl md:text-7xl font-bold mb-6">
                    Discover Paradise in<br>
                    <span class="bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent">Iloilo Province</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-2xl mx-auto opacity-90">
                    Explore the finest inland resorts across 5 districts
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <!-- <a href="?page=resorts" class="px-8 py-3 bg-white text-purple-600 rounded-full font-semibold hover:shadow-lg transition transform hover:scale-105">
                        Explore Resorts <i class="bi bi-arrow-right ms-2"></i>
                    </a> -->
                    <a href="login/guest-register.php" class="px-8 py-3 border-2 border-white rounded-full font-semibold hover:bg-white/10 transition transform hover:scale-105">
                        Get Started <i class="bi bi-person-plus ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Statistics Section -->
    <!-- <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="stat-card text-center p-6 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-building text-2xl text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalResorts; ?>+</h3>
                    <p class="text-gray-500">Premium Resorts</p>
                </div>
                <div class="stat-card text-center p-6 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-people text-2xl text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($totalGuests); ?>+</h3>
                    <p class="text-gray-500">Happy Guests</p>
                </div>
                <div class="stat-card text-center p-6 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-calendar-check text-2xl text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800"><?php echo number_format($totalBookings); ?>+</h3>
                    <p class="text-gray-500">Bookings</p>
                </div>
                <div class="stat-card text-center p-6 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl">
                    <div class="w-16 h-16 bg-gradient-to-r from-amber-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-cash-stack text-2xl text-white"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800">₱<?php echo number_format($totalRevenue / 1000, 0); ?>K+</h3>
                    <p class="text-gray-500">Total Revenue</p>
                </div>
            </div>
        </div>
    </section> -->
    
    <!-- Featured Resorts -->
    <section class="py-16 bg-gradient-to-br from-gray-50 to-purple-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-bold gradient-text mb-4">Featured Resorts</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Discover our most popular destinations across Iloilo</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach($featuredResorts as $index => $resort): ?>
                <div class="resort-card bg-white rounded-2xl overflow-hidden shadow-lg">
                    <div class="h-56 overflow-hidden relative">
                        <?php
                        $imgStmt = $db->prepare("SELECT file_name FROM images WHERE resortid = ? LIMIT 1");
                        $imgStmt->execute([$resort['resortid']]);
                        $image = $imgStmt->fetch();
                        ?>
                        <img src="uploads_flow/<?php echo htmlspecialchars($image['file_name'] ?? 'placeholder.jpg'); ?>" 
                             class="resort-image w-full h-full object-cover">
                        <div class="absolute top-4 right-4 bg-gradient-to-r from-purple-500 to-emerald-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                            Featured
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg text-gray-800 mb-1"><?php echo htmlspecialchars($resort['resortname']); ?></h3>
                        <p class="text-gray-500 text-sm mb-3">
                            <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($resort['mun']); ?>
                        </p>
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-purple-600 font-bold text-xl">₱<?php echo number_format($resort['adultEntranceFee'], 0); ?></span>
                                <span class="text-gray-400 text-sm">/adult</span>
                            </div>
                            <?php if($type == "Guest"): ?>
                            <button onclick="bookNow(<?php echo $resort['resortid']; ?>)" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition">
                                Book Now
                            </button>
                            <?php elseif(!$type): ?>
                            <button onclick="bookNow(<?php echo $resort['resortid']; ?>)" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold hover:bg-purple-700 transition">
                                Book Now
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-10">
                <a href="?page=resorts" class="inline-flex items-center gap-2 text-purple-600 font-semibold hover:gap-3 transition-all">
                    View All Resorts <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if($page == 'resorts'): ?>
    <!-- All Resorts Page -->
    <section class="py-16 pt-24">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold gradient-text mb-4">All Resorts</h1>
                <p class="text-gray-600">Explore our complete collection of inland resorts</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php
                $allResorts = $db->query("SELECT r.* FROM tb_resort r WHERE r.isLocated = 1 ORDER BY r.resortname")->fetchAll();
                foreach($allResorts as $resort):
                ?>
                <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition">
                    <div class="h-48 overflow-hidden">
                        <?php
                        $imgStmt = $db->prepare("SELECT file_name FROM images WHERE resortid = ? LIMIT 1");
                        $imgStmt->execute([$resort['resortid']]);
                        $image = $imgStmt->fetch();
                        ?>
                        <img src="uploads_flow/<?php echo htmlspecialchars($image['file_name'] ?? 'placeholder.jpg'); ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-800"><?php echo htmlspecialchars($resort['resortname']); ?></h3>
                        <p class="text-gray-500 text-sm mt-1"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($resort['mun']); ?></p>
                        <div class="mt-3 flex justify-between items-center">
                            <span class="text-purple-600 font-bold text-xl">₱<?php echo number_format($resort['adultEntranceFee'], 0); ?></span>
                            <?php if($type == "Guest"): ?>
                            <button onclick="bookNow(<?php echo $resort['resortid']; ?>)" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold">Book Now</button>
                            <?php elseif(!$type): ?>
                            <button onclick="bookNow(<?php echo $resort['resortid']; ?>)" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold">Book Now</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if($page == 'about'): ?>
    <!-- About Page -->
    <section class="py-16 pt-24">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold gradient-text mb-6">About InlandFlow</h1>
                <p class="text-xl text-gray-600">The Premier Resort Management Platform for Iloilo Province</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-white rounded-2xl shadow-lg">
                    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-building text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">30+ Municipalities</h3>
                    <p class="text-gray-500">Covering all 5 districts of Iloilo Province</p>
                </div>
                <div class="text-center p-6 bg-white rounded-2xl shadow-lg">
                    <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-tree text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">100+ Resorts</h3>
                    <p class="text-gray-500">Premium inland resorts and accommodations</p>
                </div>
                <div class="text-center p-6 bg-white rounded-2xl shadow-lg">
                    <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-people text-2xl text-amber-600"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">10,000+ Guests</h3>
                    <p class="text-gray-500">Happy customers served annually</p>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if($page == 'contact'): ?>
    <!-- Contact Page -->
    <section class="py-16 pt-24">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-bold gradient-text mb-6">Contact Us</h1>
                    <p class="text-xl text-gray-600">Get in touch with our team</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-bold mb-4">Contact Information</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-envelope text-purple-600 text-xl"></i>
                                <span>info@inlandflow.com</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="bi bi-telephone text-purple-600 text-xl"></i>
                                <span>(033) 123-4567</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="bi bi-geo-alt text-purple-600 text-xl"></i>
                                <span>Iloilo City, Philippines</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-bold mb-4">Send a Message</h3>
                        <form>
                            <div class="mb-3">
                                <input type="text" class="w-full px-4 py-2 border border-gray-200 rounded-lg" placeholder="Your Name">
                            </div>
                            <div class="mb-3">
                                <input type="email" class="w-full px-4 py-2 border border-gray-200 rounded-lg" placeholder="Your Email">
                            </div>
                            <div class="mb-3">
                                <textarea class="w-full px-4 py-2 border border-gray-200 rounded-lg" rows="4" placeholder="Your Message"></textarea>
                            </div>
                            <button type="submit" class="btn-primary w-full py-2 rounded-lg text-white">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if($type && $page == 'dashboard'): ?>
    <!-- Dashboard based on user type -->
    <?php 
    if($type == 'Guest') {
        include 'pages/guest/dashboard.php';
    } elseif($type == 'Resort') {
        include 'pages/resort/dashboard.php';
    } elseif($type == 'Municipal') {
        include 'pages/municipal/dashboard.php';
    } elseif($type == 'Provincial') {
        include 'pages/provincial/dashboard.php';
    }
    ?>
    <?php elseif($type == 'Guest' && $page == 'guest-bookings'): ?>
    <?php include 'pages/guest/my-bookings.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'booking'): ?>
    <?php include 'pages/guest/booking.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'checkout'): ?>
    <?php include 'pages/guest/checkout.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'guest-resorts'): ?>
    <?php include 'pages/guest/resorts.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'resorts'): ?>
    <?php include 'pages/guest/resorts.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'guest-gallery'): ?>
    <?php include 'pages/guest/gallery.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'guest-cart'): ?>
    <?php include 'pages/guest/cart.php'; ?>
    <?php elseif($page == 'booking'): ?>
    <?php include 'pages/guest/booking.php'; ?>
    <?php elseif($page == 'checkout'): ?>
    <?php include 'pages/guest/checkout.php'; ?>
    <?php elseif($page == 'guest-cart' || $page == 'cart'): ?>
    <?php include 'pages/guest/cart.php'; ?>
    <?php elseif($page == 'gallery'): ?>
    <?php include 'pages/guest/gallery.php'; ?>
    <?php elseif($page == 'resort-detail'): ?>
    <?php include 'pages/guest/resort-detail.php'; ?>
    <?php elseif($page == 'receipt' && isset($_GET['po_id'])): ?>
    <?php include 'api/receipt.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'resort-detail'): ?>
    <?php include 'pages/guest/resort-detail.php'; ?>
    <?php elseif($page == 'resort-detail'): ?>
    <?php include 'pages/guest/resort-detail.php'; ?>
    <?php elseif($type == 'Guest' && $page == 'profile'): ?>
    <?php include 'pages/guest/profile.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'profile'): ?>
    <?php include 'pages/resort/profile.php'; ?>
    <?php elseif($type == 'Provincial' && $page == 'profile'): ?>
    <?php include 'pages/provincial/profile.php'; ?>
    <?php elseif($type == 'Municipal' && $page == 'profile'): ?>
    <?php include 'pages/municipal/profile.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-reservations'): ?>
    <?php include 'pages/resort/reservations.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-reports'): ?>
    <?php include 'pages/resort/reports.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-accommodations'): ?>
    <?php include 'pages/resort/accommodations.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-gallery'): ?>
    <?php include 'pages/resort/gallery.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-amenities'): ?>
    <?php include 'pages/resort/amenities.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-rooms'): ?>
    <?php include 'pages/resort/rooms.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-staff'): ?>
    <?php include 'pages/resort/staff-management.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-tasks'): ?>
    <?php include 'pages/resort/task-assignments.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-analytics'): ?>
    <?php include 'pages/resort/analytics.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-guests'): ?>
    <?php include 'pages/resort/guest-management.php'; ?>
    <?php elseif($type == 'Resort' && $page == 'resort-schedule'): ?>
    <?php include 'pages/resort/schedule-tracking.php'; ?>
    <?php elseif($type == 'Provincial' && $page == 'provincial-resorts'): ?>
    <?php include 'pages/provincial/manage-resorts.php'; ?>
    <?php elseif($type == 'Provincial' && $page == 'provincial-add-municipality'): ?>
    <?php include 'pages/provincial/add-municipality.php'; ?>
    <?php elseif($type == 'Provincial' && $page == 'provincial-manage-reports'): ?>
    <?php include 'pages/provincial/manage-reports.php'; ?>
    <?php elseif($type == 'Provincial' && $page == 'provincial-profile'): ?>
    <?php include 'pages/provincial/profile.php'; ?>
    <?php elseif($type == 'Municipal' && $page == 'municipal-reports'): ?>
    <?php include 'pages/municipal/resort-reports.php'; ?>
    <?php elseif($type == 'Municipal' && $page == 'municipal-add-resort'): ?>
    <?php include 'pages/municipal/add-resort.php'; ?>
    <?php elseif($type == 'Municipal' && $page == 'municipal-manage-locations'): ?>
    <?php include 'pages/municipal/manage-locations.php'; ?>
    <?php elseif($type == 'Municipal' && $page == 'municipal-profile'): ?>
    <?php include 'pages/municipal/profile.php'; ?>
    <?php elseif($type == 'Municipal' && $page == 'municipal-resort-reports'): ?>
    <?php include 'pages/municipal/resort-reports.php'; ?>
    <?php endif; ?>
</main>

<!-- Footer -->
<footer class="bg-gray-900 text-white pt-12 pb-6 mt-20">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-xl flex items-center justify-center">
                        <i class="bi bi-water-wave text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-emerald-400 bg-clip-text text-transparent">InlandFlow</span>
                </div>
                <p class="text-gray-400 text-sm">Premium inland resort management system for Iloilo Province.</p>
            </div>
            <div>
                <h4 class="font-semibold text-lg mb-4">Quick Links</h4>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li><a href="?page=home" class="hover:text-white transition">Home</a></li>
                    <li><a href="?page=resorts" class="hover:text-white transition">Resorts</a></li>
                    <li><a href="?page=about" class="hover:text-white transition">About</a></li>
                    <li><a href="?page=contact" class="hover:text-white transition">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-lg mb-4">Contact</h4>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li><i class="bi bi-envelope me-2"></i> info@inlandflow.com</li>
                    <li><i class="bi bi-telephone me-2"></i> (033) 123-4567</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-lg mb-4">Follow Us</h4>
                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="w-9 h-9 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 pt-6 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> InlandFlow. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
// Mobile menu toggle
document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
    const icon = this.querySelector('i');
    if (icon.classList.contains('bi-list')) {
        icon.classList.remove('bi-list');
        icon.classList.add('bi-x-lg');
    } else {
        icon.classList.remove('bi-x-lg');
        icon.classList.add('bi-list');
    }
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Book now function
function bookNow(resortId) {
    <?php if($type == "Guest" || !$type): ?>
    window.location.href = '?page=booking&resort=' + resortId;
    <?php else: ?>
    Swal.fire({
        icon: 'info',
        title: 'Login Required',
        text: 'Please login as a guest to book resorts',
        showCancelButton: true,
        confirmButtonText: 'Login Now',
        cancelButtonText: 'Register'
    }).then((result) => {
        if(result.isConfirmed) {
            window.location.href = 'login/guest-login.php';
        } else if(result.dismiss === 'cancel') {
            window.location.href = 'login/guest-register.php';
        }
    });
    <?php endif; ?>
}

// Toast function
function showToast(message, type = 'success') {
    Swal.fire({
        text: message,
        icon: type,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Make functions global
window.showToast = showToast;
window.bookNow = bookNow;
</script>

</body>
</html>