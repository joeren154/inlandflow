<?php
session_start();
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
    <title>Guest Registration - InlandFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 32px;
            backdrop-filter: blur(10px);
        }
        .input-group-custom {
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .input-group-custom:focus-within {
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
        }
        .input-group-custom input, .input-group-custom textarea, .input-group-custom select {
            border: none;
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 12px;
            width: 100%;
        }
        .btn-register {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            border: none;
            padding: 14px;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step .circle {
            width: 40px;
            height: 40px;
            background: #e2e8f0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #64748b;
            position: relative;
            z-index: 1;
        }
        .step.active .circle {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }
        .step.completed .circle {
            background: #10b981;
            color: white;
        }
        .step .label {
            font-size: 12px;
            margin-top: 8px;
            color: #64748b;
        }
        .step.active .label {
            color: #4facfe;
            font-weight: 600;
        }
        .step:not(:last-child):before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e2e8f0;
            z-index: 0;
        }
        .step.completed:not(:last-child):before {
            background: #10b981;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="container max-w-2xl mx-auto">
        <div class="text-center mb-6">
            <a href="guest-login.php" class="text-white/80 hover:text-white transition">
                <i class="bi bi-arrow-left me-2"></i> Back to Login
            </a>
        </div>
        
        <div class="register-container p-8 shadow-2xl">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-person-plus text-4xl text-white"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">Create Guest Account</h2>
                <p class="text-slate-500 text-sm mt-1">Join InlandFlow to discover and book amazing resorts</p>
            </div>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step1">
                    <div class="circle">1</div>
                    <div class="label">Personal Info</div>
                </div>
                <div class="step" id="step2">
                    <div class="circle">2</div>
                    <div class="label">Contact Info</div>
                </div>
                <div class="step" id="step3">
                    <div class="circle">3</div>
                    <div class="label">Account Setup</div>
                </div>
            </div>
            
            <form id="registerForm" method="POST">
                <!-- Step 1: Personal Information -->
                <div id="step1Content" class="step-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">First Name *</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="text" name="firstname" placeholder="Enter first name" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Middle Name</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="text" name="middlename" placeholder="Enter middle name">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Last Name *</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="text" name="lastname" placeholder="Enter last name" required>
                            </div>
                        </div>
                        <!-- <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Date of Birth</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="date" name="birthdate">
                            </div>
                        </div> -->
                        <div class="mb-3 md:col-span-2">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Address *</label>
                            <div class="input-group-custom bg-slate-50">
                                <textarea name="address" rows="2" placeholder="Enter your complete address" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="button" class="next-step px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-xl">
                            Next <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Contact Information -->
                <div id="step2Content" class="step-content" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Email Address *</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="email" name="email" placeholder="you@example.com" required>
                            </div>
                        </div> -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Contact Number *</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="tel" name="phone" placeholder="09xxxxxxxxx" required>
                            </div>
                        </div>
                        <!-- <div class="mb-3 md:col-span-2">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Emergency Contact</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="tel" name="emergency_contact" placeholder="Emergency contact number">
                            </div>
                        </div> -->
                    </div>
                    <div class="flex justify-between mt-4">
                        <button type="button" class="prev-step px-6 py-2 bg-slate-200 text-slate-700 rounded-xl">
                            <i class="bi bi-arrow-left me-2"></i> Previous
                        </button>
                        <button type="button" class="next-step px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-xl">
                            Next <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Account Setup -->
                <div id="step3Content" class="step-content" style="display: none;">
                    <div class="grid grid-cols-1 gap-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Username *</label>
                            <div class="input-group-custom bg-slate-50">
                                <input type="text" name="reg_username" id="username" placeholder="Choose a username" required>
                            </div>
                            <small id="usernameCheck" class="text-slate-500 text-xs"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Password *</label>
                            <div class="input-group-custom bg-slate-50 position-relative">
                                <input type="password" name="reg_password" id="password" placeholder="Create a password" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer" onclick="togglePassword('password', 'toggleIcon1')">
                                    <i class="bi bi-eye-slash" id="toggleIcon1"></i>
                                </span>
                            </div>
                            <div class="password-strength mt-2" id="passwordStrength"></div>
                            <small class="text-slate-500 text-xs">Password must be at least 6 characters</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 mb-2">Confirm Password *</label>
                            <div class="input-group-custom bg-slate-50 position-relative">
                                <input type="password" name="reg_confirm_password" id="confirmPassword" placeholder="Confirm your password" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3 cursor-pointer" onclick="togglePassword('confirmPassword', 'toggleIcon2')">
                                    <i class="bi bi-eye-slash" id="toggleIcon2"></i>
                                </span>
                            </div>
                            <small id="passwordMatch" class="text-slate-500 text-xs"></small>
                        </div>
                    </div>
                    <div class="flex justify-between mt-4">
                        <button type="button" class="prev-step px-6 py-2 bg-slate-200 text-slate-700 rounded-xl">
                            <i class="bi bi-arrow-left me-2"></i> Previous
                        </button>
                        <button type="submit" class="btn-register w-auto px-6 py-2 text-white">
                            <i class="bi bi-check-circle me-2"></i> Register Account
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="mt-6 pt-4 border-t border-slate-200 text-center">
                <p class="text-slate-500 text-sm">Already have an account? <a href="guest-login.php" class="text-blue-600 font-semibold">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        let currentStep = 1;
        
        function showStep(step) {
            $('.step-content').hide();
            $(`#step${step}Content`).show();
            
            $('.step').removeClass('active');
            $(`#step${step}`).addClass('active');
            
            for(let i = 1; i < step; i++) {
                $(`#step${i}`).addClass('completed');
            }
            for(let i = step + 1; i <= 3; i++) {
                $(`#step${i}`).removeClass('completed');
            }
        }
        
        $('.next-step').on('click', function() {
            if(validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });
        
        $('.prev-step').on('click', function() {
            currentStep--;
            showStep(currentStep);
        });
        
        function validateStep(step) {
            if(step === 1) {
                const firstname = $('input[name="firstname"]').val();
                const lastname = $('input[name="lastname"]').val();
                const address = $('textarea[name="address"]').val();
                
                if(!firstname || !lastname || !address) {
                    Swal.fire('Error', 'Please fill in all required fields', 'error');
                    return false;
                }
            } else if(step === 2) {
 
                const phone = $('input[name="phone"]').val();
                
                const phoneRegex = /^09\d{9}$/;
                
                if(!phone || !phoneRegex.test(phone)) {
                    Swal.fire('Error', 'Please enter a valid Philippine mobile number (09xxxxxxxxx)', 'error');
                    return false;
                }
            }
            return true;
        }
        
        // Password strength checker
        $('#password').on('keyup', function() {
            const password = $(this).val();
            const strengthBar = $('#passwordStrength');
            let strength = 0;
            
            if(password.length >= 6) strength++;
            if(password.match(/[a-z]+/)) strength++;
            if(password.match(/[A-Z]+/)) strength++;
            if(password.match(/[0-9]+/)) strength++;
            if(password.match(/[$@#&!]+/)) strength++;
            
            let width = (strength / 5) * 100;
            let color = '#ef4444';
            if(strength >= 3) color = '#f59e0b';
            if(strength >= 4) color = '#10b981';
            
            strengthBar.css({
                'width': width + '%',
                'background': color
            });
        });
        
        // Password match checker
        $('#confirmPassword, #password').on('keyup', function() {
            const password = $('#password').val();
            const confirm = $('#confirmPassword').val();
            
            if(password === confirm && password.length > 0) {
                $('#passwordMatch').html('<i class="bi bi-check-circle text-green-500"></i> Passwords match').css('color', '#10b981');
            } else if(confirm.length > 0) {
                $('#passwordMatch').html('<i class="bi bi-x-circle text-red-500"></i> Passwords do not match').css('color', '#ef4444');
            } else {
                $('#passwordMatch').html('');
            }
        });
        
        // Username availability check
        let usernameTimeout;
        $('#username').on('keyup', function() {
            clearTimeout(usernameTimeout);
            const username = $(this).val();
            
            if(username.length >= 3) {
                usernameTimeout = setTimeout(function() {
                    $.ajax({
                        url: '../api/auth/check-username.php',
                        type: 'POST',
                        data: {username: username},
                        dataType: 'json',
                        success: function(res) {
                            if(res.exists) {
                                $('#usernameCheck').html('<i class="bi bi-x-circle text-red-500"></i> Username already taken').css('color', '#ef4444');
                                $('button[type="submit"]').prop('disabled', true);
                            } else {
                                $('#usernameCheck').html('<i class="bi bi-check-circle text-green-500"></i> Username available').css('color', '#10b981');
                                $('button[type="submit"]').prop('disabled', false);
                            }
                        },
                        error: function() {
                            $('#usernameCheck').html('');
                            $('button[type="submit"]').prop('disabled', false);
                        }
                    });
                }, 500);
            }
        });
        
        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if(field.type === 'password') {
                field.type = 'text';
                icon.className = 'bi bi-eye';
            } else {
                field.type = 'password';
                icon.className = 'bi bi-eye-slash';
            }
        }
        
        // Form submission
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            const password = $('#password').val();
            const confirm = $('#confirmPassword').val();
            
            if(password !== confirm) {
                Swal.fire('Error', 'Passwords do not match', 'error');
                return;
            }
            
            if(password.length < 6) {
                Swal.fire('Error', 'Password must be at least 6 characters', 'error');
                return;
            }
            
            Swal.fire({
                title: 'Creating Account...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '../api/auth/guest-register.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if(res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful!',
                            text: 'Your account has been created. Redirecting...',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            const urlParams = new URLSearchParams(window.location.search);
                            const redirect = urlParams.get('redirect');
                            if(redirect === 'checkout') {
                                window.location.href = '../index.php?page=checkout';
                            } else {
                                window.location.href = '../index.php';
                            }
                        });
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Registration failed. Please try again.', 'error');
                }
            });
        });
    </script>
</body>
</html>