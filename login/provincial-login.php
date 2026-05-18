<?php
session_start();
if(isset($_SESSION['type_of_user']) && $_SESSION['type_of_user'] == 'Provincial') {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provincial Login - InlandFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            backdrop-filter: blur(10px);
        }
        .input-group-custom {
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        .input-group-custom:focus-within {
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.2);
        }
        .input-group-custom input {
            border: none;
            background: #f8f9fa;
            padding: 14px 20px;
            border-radius: 16px;
        }
        .btn-login {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border: none;
            padding: 14px;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="container max-w-md mx-auto">
        <div class="text-center mb-8">
            <a href="index.php" class="text-white/80 hover:text-white transition">
                <i class="bi bi-arrow-left me-2"></i> Back to Login Options
            </a>
        </div>
        
        <div class="login-container p-8 shadow-2xl">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-building text-4xl text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">Provincial Login</h2>
                <p class="text-slate-500 text-sm mt-1">Access Provincial Administrator Dashboard</p>
            </div>
            
            <form id="provincialLoginForm">
                <div class="mb-4">
                    <label class="form-label fw-semibold text-slate-700 mb-2">Username</label>
                    <div class="input-group-custom bg-slate-50">
                        <input type="text" name="username" class="form-control bg-transparent" placeholder="Enter username" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-slate-700 mb-2">Password</label>
                    <div class="input-group-custom bg-slate-50 position-relative">
                        <input type="password" name="password" id="password" class="form-control bg-transparent" placeholder="Enter password" required>
                        <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer" onclick="togglePassword()">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login w-full text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Login as Provincial
                </button>
            </form>
            
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if(password.type === 'password') {
                password.type = 'text';
                icon.className = 'bi bi-eye';
            } else {
                password.type = 'password';
                icon.className = 'bi bi-eye-slash';
            }
        }
        
        $('#provincialLoginForm').on('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Logging in...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.post('../api/auth/provincial-auth.php', $(this).serialize(), function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful!',
                        text: 'Redirecting to dashboard...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '../index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: response.message || 'Invalid username or password'
                    });
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.'
                });
            });
        });
    </script>
</body>
</html>