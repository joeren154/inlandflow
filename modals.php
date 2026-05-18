<?php
include_once 'connection.php';
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title text-2xl font-bold"><i class="bi bi-water-wave me-2"></i>InlandFlow Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <form id="loginForm">
                    <div class="mb-4">
                        <label class="form-label text-sm font-semibold">Username</label>
                        <input type="text" name="username" class="glass-input w-full py-3 px-4 rounded-xl" required placeholder="Enter username">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-sm font-semibold">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="loginPassword" class="glass-input w-full py-3 px-4 rounded-xl" required placeholder="Enter password">
                            <span class="absolute end-0 top-1/2 -translate-y-1/2 me-3 cursor-pointer" onclick="togglePassword('loginPassword', 'loginToggle')">
                                <i class="bi bi-eye" id="loginToggle"></i>
                            </span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-hero w-full py-3 rounded-xl font-semibold bg-gradient-to-r from-purple-500 to-emerald-500 text-white">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>
                <div class="text-center mt-4">
                    <p class="text-slate-600">Don't have an account? 
                        <a href="#" onclick="showRegisterModal()" class="text-purple-600 font-semibold">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title text-2xl font-bold"><i class="bi bi-person-plus me-2"></i>Guest Registration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-6">
                <form id="registerForm">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">First Name</label>
                            <input type="text" name="firstname" class="glass-input w-full py-3 px-4 rounded-xl" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">Middle Name</label>
                            <input type="text" name="middlename" class="glass-input w-full py-3 px-4 rounded-xl">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">Last Name</label>
                            <input type="text" name="lastname" class="glass-input w-full py-3 px-4 rounded-xl" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">Username</label>
                            <input type="text" name="reg_username" class="glass-input w-full py-3 px-4 rounded-xl" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">Password</label>
                            <input type="password" name="reg_password" class="glass-input w-full py-3 px-4 rounded-xl" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">Confirm Password</label>
                            <input type="password" name="reg_confirm_password" class="glass-input w-full py-3 px-4 rounded-xl" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">Email</label>
                            <input type="email" name="email" class="glass-input w-full py-3 px-4 rounded-xl" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold">Phone Number</label>
                            <input type="tel" name="phone" class="glass-input w-full py-3 px-4 rounded-xl" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-sm font-semibold">Address</label>
                        <textarea name="address" class="glass-input w-full py-3 px-4 rounded-xl" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-hero w-full py-3 rounded-xl font-semibold bg-gradient-to-r from-purple-500 to-emerald-500 text-white">
                        <i class="bi bi-check-circle me-2"></i>Register
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title font-bold" id="alertModalLabel">Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-info-circle text-5xl text-info" id="alertIcon"></i>
                <p class="mt-3" id="alertMessage"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title font-bold" id="confirmModalLabel">Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-question-circle text-5xl text-warning" id="confirmIcon"></i>
                <p class="mt-3" id="confirmMessage"></p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirmCancel">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmOk">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content glass-modal">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3" id="loadingMessage">Loading...</p>
            </div>
        </div>
    </div>
</div>

<!-- Cart Add Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title text-2xl font-bold">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="cartModalBody">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
</div>

<!-- Browse Resort Modal -->
<div class="modal fade" id="browseResortModal" tabindex="-1" role="dialog" aria-labelledby="browseResortModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass-modal">
            <div class="modal-header border-0">
                <h5 class="modal-title text-2xl font-bold">Resort Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="browseResortBody">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
</div>

<script>
// Toast function
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const colors = {
        'success': 'bg-success',
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info'
    };
    const icons = {
        'success': 'bi-check-circle',
        'error': 'bi-x-circle',
        'warning': 'bi-exclamation-triangle',
        'info': 'bi-info-circle'
    };
    toast.className = `toast align-items-center text-white ${colors[type]} fade show`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body"><i class="bi ${icons[type]} me-2"></i>${message}</div>
            <button type="button" class="btn-close btn-close-white me-auto m-2" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Modal alert
function modal_alert(message, type = 'info', duration = 3000) {
    document.getElementById('alertMessage').textContent = message;
    const icons = {success: 'bi-check-circle text-success', error: 'bi-x-circle text-danger', warning: 'bi-exclamation-triangle text-warning', info: 'bi-info-circle text-info'};
    document.getElementById('alertIcon').className = icons[type] || icons.info;
    $('#alertModal').modal('show');
    if(duration) setTimeout(() => $('#alertModal').modal('hide'), duration);
}

// Toggle password visibility
function togglePassword(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);
    if(input.type === 'password') {
        input.type = 'text';
        toggle.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        toggle.className = 'bi bi-eye';
    }
}

// Show register modal
function showRegisterModal() {
    $('#loginModal').modal('hide');
    setTimeout(() => $('#registerModal').modal('show'), 300);
}

// Show login modal
function showLoginModal() {
    $('#registerModal').modal('hide');
    setTimeout(() => $('#loginModal').modal('show'), 300);
}

// Login form handler
$('#loginForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $('#loadingModal').modal('show');
    $.post('user-login/actions/login.php', formData, function(response) {
        $('#loadingModal').modal('hide');
        var res = JSON.parse(response);
        if(res.status === 'success') {
            showToast('Login successful!', 'success');
            $('#loginModal').modal('hide');
            location.reload();
        } else {
            showToast(res.message || 'Invalid credentials', 'error');
        }
    });
});

// Register form handler
$('#registerForm').submit(function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    var password = $('input[name="reg_password"]').val();
    var confirm = $('input[name="reg_confirm_password"]').val();
    if(password !== confirm) {
        showToast('Passwords do not match', 'error');
        return;
    }
    $('#loadingModal').modal('show');
    $.post('user-login/actions/register-guest.php', formData, function(response) {
        $('#loadingModal').modal('hide');
        var res = JSON.parse(response);
        if(res.status === 'success') {
            showToast('Registration successful!', 'success');
            $('#registerModal').modal('hide');
            location.reload();
        } else {
            showToast(res.message || 'Registration failed', 'error');
        }
    });
});
</script>