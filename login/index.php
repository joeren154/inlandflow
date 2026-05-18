<?php
session_start();
// If already logged in, redirect to appropriate dashboard
if(isset($_SESSION['type_of_user'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InlandFlow - Login Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        .option-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .option-icon i {
            font-size: 40px;
            color: white;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="container max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <div class="inline-block bg-white/20 backdrop-blur-lg rounded-2xl px-8 py-4 mb-6">
                <i class="bi bi-water-wave text-5xl text-white"></i>
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">Welcome to InlandFlow</h1>
            <p class="text-xl text-white/90">Select your account type to continue</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Provincial Login -->
            <a href="provincial-login.php" class="login-card bg-white p-6 text-center block shadow-lg hover:shadow-2xl transition-all">
                <div class="option-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Provincial</h3>
                <p class="text-slate-500 text-sm">Provincial Administrator Access</p>
                <div class="mt-4 text-purple-600">
                    Login <i class="bi bi-arrow-right"></i>
                </div>
            </a>
            
            <!-- Municipal Login -->
            <a href="municipal-login.php" class="login-card bg-white p-6 text-center block shadow-lg hover:shadow-2xl transition-all">
                <div class="option-icon">
                    <i class="bi bi-building"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Municipal</h3>
                <p class="text-slate-500 text-sm">Municipal Administrator Access</p>
                <div class="mt-4 text-purple-600">
                    Login <i class="bi bi-arrow-right"></i>
                </div>
            </a>
            
            <!-- Resort Login -->
            <a href="resort-login.php" class="login-card bg-white p-6 text-center block shadow-lg hover:shadow-2xl transition-all">
                <div class="option-icon">
                    <i class="bi bi-tree"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Resort Owner</h3>
                <p class="text-slate-500 text-sm">Resort Management Access</p>
                <div class="mt-4 text-purple-600">
                    Login <i class="bi bi-arrow-right"></i>
                </div>
            </a>
            
            <!-- Guest Login -->
            <a href="guest-login.php" class="login-card bg-white p-6 text-center block shadow-lg hover:shadow-2xl transition-all">
                <div class="option-icon">
                    <i class="bi bi-person"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Guest</h3>
                <p class="text-slate-500 text-sm">Book and Explore Resorts</p>
                <div class="mt-4 text-purple-600">
                    Login <i class="bi bi-arrow-right"></i>
                </div>
            </a>
        </div>
        
        <div class="text-center mt-12 text-white/80 text-sm">
            <p>&copy; 2026 InlandFlow - Premium Inland Resort Management System</p>
        </div>
    </div>
</body>
</html>