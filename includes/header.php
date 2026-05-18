<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InlandFlow - <?php echo $pageTitle ?? 'Resort Management System'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #f5f3ff 0%, #ecfdf5 100%); }
        .navbar { transition: all 0.3s ease; }
        .navbar.scrolled { background: rgba(17, 24, 39, 0.98) !important; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .nav-link { transition: all 0.3s ease; position: relative; }
        .nav-link:hover { color: #c084fc !important; transform: translateY(-2px); }
        .btn-primary { background: linear-gradient(135deg, #8b5cf6, #7c3aed); transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3); }
        .btn-secondary { background: linear-gradient(135deg, #10b981, #059669); transition: all 0.3s ease; }
        .btn-secondary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3); }
        .resort-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .resort-card:hover { transform: translateY(-8px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .resort-card:hover .resort-image { transform: scale(1.1); }
        .resort-image { transition: transform 0.5s ease; }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .gradient-text { background: linear-gradient(135deg, #8b5cf6, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative; overflow: hidden; }
        .hero-section::before { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom; background-size: cover; opacity: 0.3; }
        .dropdown-menu { opacity: 0; visibility: hidden; transition: all 0.3s ease; transform: translateY(-10px); }
        .group:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        @keyframes slideInRight { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        .toast-notification { animation: slideInRight 0.3s ease-out; }
        .loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; }
        .spinner { width: 50px; height: 50px; border: 3px solid rgba(255, 255, 255, 0.3); border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 4px; }
        @media (max-width: 768px) { .mobile-menu-open { max-height: 300px; opacity: 1; } }
    </style>
</head>
<body>
<nav class="navbar fixed top-0 w-full bg-gray-900 shadow-lg z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            <a href="index.php" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg transition-transform group-hover:scale-110">
                    <i class="bi bi-water-wave text-white text-xl"></i>
                </div>
                <span class="text-xl font-bold bg-gradient-to-r from-purple-400 to-emerald-400 bg-clip-text text-transparent">InlandFlow</span>
            </a>
            <div class="hidden md:flex items-center gap-1">
                <?php if(!isLoggedIn()): ?>
                    <a href="?page=home" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">Home</a>
                    <a href="?page=resorts" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">Resorts</a>
                    <a href="?page=gallery" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">Gallery</a>
                    <a href="?page=about" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">About</a>
                    <a href="?page=contact" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">Contact</a>
                    <a href="?page=login" class="ml-4 px-5 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full hover:shadow-lg transition transform hover:scale-105">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                <?php else: ?>
                    <?php $userType = $_SESSION['user_type']; ?>
                    <a href="?page=dashboard" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                    <?php if($userType == 'guest'): ?>
                        <a href="?page=my-bookings" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-calendar-check me-1"></i>My Bookings
                        </a>
                        <a href="?page=guest-gallery" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-images me-1"></i>Gallery
                        </a>
                    <?php elseif($userType == 'resort'): ?>
                        <a href="?page=reservations" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-clipboard-list me-1"></i>Reservations
                        </a>
                        <a href="?page=reports" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-graph-up me-1"></i>Reports
                        </a>
                        <a href="?page=resort-gallery" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-images me-1"></i>Gallery
                        </a>
                    <?php elseif($userType == 'municipal'): ?>
                        <a href="?page=reports" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-file-text me-1"></i>Reports
                        </a>
                    <?php elseif($userType == 'provincial'): ?>
                        <a href="?page=resorts" class="nav-link px-4 py-2 text-gray-300 hover:text-purple-400 rounded-lg transition">
                            <i class="bi bi-building me-1"></i>Resorts
                        </a>
                    <?php endif; ?>
                    <div class="relative group ml-2">
                        <button class="flex items-center gap-2 px-3 py-2 bg-gray-800 rounded-full hover:bg-gray-700 transition">
                            <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-emerald-500 rounded-full flex items-center justify-center">
                                <i class="bi bi-person-fill text-white text-sm"></i>
                            </div>
                            <span class="text-gray-300 text-sm"><?php $ud = getUserData(); echo htmlspecialchars($ud['username'] ?? $ud['Username'] ?? $ud['FirstName'] ?? 'User'); ?></span>
                            <i class="bi bi-chevron-down text-gray-400 text-xs"></i>
                        </button>
                        <div class="dropdown-menu absolute right-0 w-56 bg-white rounded-xl shadow-xl py-2 mt-2 group-hover:opacity-100 group-hover:visible z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800"><?php echo ucfirst($userType); ?> Account</p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($ud['username'] ?? $ud['Username'] ?? ''); ?></p>
                            </div>
                            <a href="?page=profile" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
                                <i class="bi bi-person-circle"></i><span>My Profile</span>
                            </a>
                            <?php if($userType == 'resort'): ?>
                            <a href="?page=resort-gallery" class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
                                <i class="bi bi-images"></i><span>Gallery</span>
                            </a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="?page=logout" class="flex items-center gap-3 px-4 py-2 text-red-600 hover:bg-red-50 transition">
                                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button id="mobileMenuBtn" class="md:hidden text-gray-300 text-2xl">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <div id="mobileMenu" class="hidden md:hidden pb-4">
            <?php if(!isLoggedIn()): ?>
                <a href="?page=home" class="block py-2 text-gray-300 hover:text-purple-400 transition">Home</a>
                <a href="?page=resorts" class="block py-2 text-gray-300 hover:text-purple-400 transition">Resorts</a>
                <a href="?page=gallery" class="block py-2 text-gray-300 hover:text-purple-400 transition">Gallery</a>
                <a href="?page=about" class="block py-2 text-gray-300 hover:text-purple-400 transition">About</a>
                <a href="?page=contact" class="block py-2 text-gray-300 hover:text-purple-400 transition">Contact</a>
                <a href="?page=login" class="block mt-2 px-4 py-2 bg-purple-600 text-white rounded-lg text-center">Login</a>
            <?php else: ?>
                <a href="?page=dashboard" class="block py-2 text-gray-300">Dashboard</a>
                <a href="?page=gallery" class="block py-2 text-gray-300">Gallery</a>
                <a href="?page=profile" class="block py-2 text-gray-300">Profile</a>
                <a href="?page=logout" class="block mt-2 px-4 py-2 bg-red-600 text-white rounded-lg text-center">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="pt-16">