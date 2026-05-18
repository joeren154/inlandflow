/* ========================================
   INLANDFLOW - Complete JavaScript
   ======================================== */

// Global Variables
let currentPage = 1;
let itemsPerPage = 10;
let currentUserType = null;

// Document Ready
$(document).ready(function() {
    initializeNavbar();
    initializeAOS();
    initializeSwiper();
    initializeDataTables();
    initializeForms();
    initializeCharts();
});

// Initialize Navbar Scroll Effect
function initializeNavbar() {
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });
    
    // Mobile menu toggle
    $('#mobileMenuBtn').on('click', function() {
        $('#mobileMenu').toggleClass('hidden');
        $(this).find('i').toggleClass('bi-list bi-x-lg');
    });
}

// Initialize AOS Animations
function initializeAOS() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }
}

// Initialize Swiper Slider
function initializeSwiper() {
    if (typeof Swiper !== 'undefined' && $('.hero-swiper').length) {
        new Swiper('.hero-swiper', {
            loop: true,
            speed: 800,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            },
            effect: 'fade'
        });
    }
}

// Initialize Data Tables
function initializeDataTables() {
    $('.data-table').each(function() {
        const $table = $(this);
        const $container = $table.closest('.table-container');
        
        // Add search if search input exists
        const $search = $('#tableSearch');
        if ($search.length) {
            $search.on('keyup', debounce(function() {
                filterTable($table, $(this).val());
            }, 300));
        }
        
        // Add pagination
        const totalRows = $table.find('tbody tr').length;
        if (totalRows > itemsPerPage) {
            addPagination($table);
        }
    });
}

// Filter Table
function filterTable($table, searchTerm) {
    const term = searchTerm.toLowerCase();
    $table.find('tbody tr').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(term) > -1);
    });
    updatePagination($table);
}

// Add Pagination
function addPagination($table) {
    const $container = $table.closest('.table-container');
    if ($container.find('.pagination').length) return;
    
    const totalRows = $table.find('tbody tr:visible').length;
    const totalPages = Math.ceil(totalRows / itemsPerPage);
    
    const $pagination = $(`
        <div class="pagination">
            <button class="pagination-btn prev-page" ${currentPage === 1 ? 'disabled' : ''}>
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="pagination-info text-sm text-gray-500">
                Page ${currentPage} of ${totalPages}
            </span>
            <button class="pagination-btn next-page" ${currentPage === totalPages ? 'disabled' : ''}>
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    `);
    
    $container.append($pagination);
    
    $pagination.find('.prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            updateTableDisplay($table, currentPage);
        }
    });
    
    $pagination.find('.next-page').on('click', function() {
        const totalPages = Math.ceil($table.find('tbody tr:visible').length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updateTableDisplay($table, currentPage);
        }
    });
    
    updateTableDisplay($table, currentPage);
}

// Update Table Display
function updateTableDisplay($table, page) {
    const $rows = $table.find('tbody tr:visible');
    const start = (page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    
    $rows.hide();
    $rows.slice(start, end).show();
    
    const $container = $table.closest('.table-container');
    const totalPages = Math.ceil($rows.length / itemsPerPage);
    $container.find('.pagination-info').text(`Page ${page} of ${totalPages}`);
    $container.find('.prev-page').prop('disabled', page === 1);
    $container.find('.next-page').prop('disabled', page === totalPages);
}

// Update Pagination
function updatePagination($table) {
    const $container = $table.closest('.table-container');
    $container.find('.pagination').remove();
    currentPage = 1;
    addPagination($table);
}

// Initialize Forms
function initializeForms() {
    // Real-time validation
    $('input[required], select[required], textarea[required]').on('blur', function() {
        validateField($(this));
    });
    
    // Password match validation
    $('#confirmPassword, #password').on('keyup', function() {
        const password = $('#password').val();
        const confirm = $('#confirmPassword').val();
        if (password && confirm) {
            if (password === confirm) {
                $('#confirmPassword').removeClass('error');
                $('#passwordMatch').html('<i class="bi bi-check-circle text-green-500"></i> Passwords match').css('color', '#059669');
            } else {
                $('#confirmPassword').addClass('error');
                $('#passwordMatch').html('<i class="bi bi-x-circle text-red-500"></i> Passwords do not match').css('color', '#dc2626');
            }
        }
    });
    
    // Phone number validation
    $('input[type="tel"]').on('blur', function() {
        const phone = $(this).val();
        const phoneRegex = /^09\d{9}$/;
        if (phone && !phoneRegex.test(phone)) {
            $(this).addClass('error');
            showToast('Invalid phone number. Must start with 09 and have 11 digits', 'error');
        } else {
            $(this).removeClass('error');
        }
    });
    
    // Email validation
    $('input[type="email"]').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $(this).addClass('error');
            showToast('Invalid email address', 'error');
        } else {
            $(this).removeClass('error');
        }
    });
}

// Validate Single Field
function validateField($field) {
    if (!$field.val()) {
        $field.addClass('error');
        return false;
    } else {
        $field.removeClass('error');
        return true;
    }
}

// Initialize Charts
function initializeCharts() {
    if (typeof Chart === 'undefined') return;
    
    // Revenue Chart
    if ($('#revenueChart').length) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: (ctx) => `₱${ctx.raw.toLocaleString()}` } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (value) => `₱${value.toLocaleString()}` } }
                }
            }
        });
    }
    
    // Booking Chart
    if ($('#bookingChart').length) {
        const ctx = document.getElementById('bookingChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Bookings',
                    data: [],
                    backgroundColor: 'rgba(139, 92, 246, 0.5)',
                    borderColor: '#8b5cf6',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'top' } }
            }
        });
    }
}

// Toast Notification
function showToast(message, type = 'success') {
    const toastContainer = $('#toastContainer');
    if (!toastContainer.length) {
        $('body').append('<div id="toastContainer" class="toast-container"></div>');
    }
    
    const icons = {
        success: 'bi-check-circle',
        error: 'bi-x-circle',
        info: 'bi-info-circle',
        warning: 'bi-exclamation-triangle'
    };
    
    const toast = $(`
        <div class="toast toast-${type}">
            <i class="${icons[type]}"></i>
            <span>${message}</span>
        </div>
    `);
    
    $('#toastContainer').append(toast);
    
    setTimeout(() => {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 3000);
}

// Modal Alert
function modalAlert(message, type = 'info') {
    const icons = {
        success: 'bi-check-circle text-green-500',
        error: 'bi-x-circle text-red-500',
        info: 'bi-info-circle text-blue-500',
        warning: 'bi-exclamation-triangle text-yellow-500'
    };
    
    Swal.fire({
        icon: type,
        title: type.charAt(0).toUpperCase() + type.slice(1),
        text: message,
        confirmButtonColor: '#8b5cf6'
    });
}

// Confirm Dialog
function confirmDialog(message, onConfirm) {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && onConfirm) {
            onConfirm();
        }
    });
}

// Show Loading
function showLoading(message = 'Loading...') {
    if ($('#loadingOverlay').length) return;
    
    const overlay = $(`
        <div id="loadingOverlay" class="loading-overlay">
            <div class="text-center">
                <div class="spinner mx-auto mb-4"></div>
                <p class="text-white">${message}</p>
            </div>
        </div>
    `);
    
    $('body').append(overlay);
    overlay.fadeIn(200);
}

// Hide Loading
function hideLoading() {
    $('#loadingOverlay').fadeOut(200, function() {
        $(this).remove();
    });
}

// AJAX Form Submission
function submitFormAjax(formId, url, callback) {
    const $form = $(formId);
    
    if (!validateForm($form)) {
        return false;
    }
    
    showLoading('Processing...');
    
    $.ajax({
        url: url,
        type: 'POST',
        data: $form.serialize(),
        success: function(response) {
            hideLoading();
            try {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                    showToast(res.message || 'Operation successful', 'success');
                    if (callback) callback(res);
                    if (res.redirect) setTimeout(() => window.location.href = res.redirect, 1500);
                    if (res.reload) setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(res.message || 'Operation failed', 'error');
                }
            } catch (e) {
                showToast('Operation completed', 'success');
                if (callback) callback(response);
            }
        },
        error: function() {
            hideLoading();
            showToast('An error occurred. Please try again.', 'error');
        }
    });
    
    return true;
}

// Validate Form
function validateForm($form) {
    let isValid = true;
    
    $form.find('[required]').each(function() {
        if (!$(this).val()) {
            $(this).addClass('error');
            isValid = false;
        } else {
            $(this).removeClass('error');
        }
    });
    
    if (!isValid) {
        showToast('Please fill in all required fields', 'error');
    }
    
    return isValid;
}

// Export Table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const $table = $(tableId);
    const $rows = $table.find('tr');
    const csvData = [];
    
    $rows.each(function() {
        const $cells = $(this).find('th, td');
        const rowData = [];
        $cells.each(function() {
            let text = $(this).text().trim();
            text = text.replace(/[,\n]/g, ' ');
            rowData.push(text);
        });
        csvData.push(rowData.join(','));
    });
    
    const csvContent = csvData.join('\n');
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.click();
    URL.revokeObjectURL(url);
}

// Print Element
function printElement(elementId) {
    const printContent = document.getElementById(elementId).innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold">InlandFlow Report</h2>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
            ${printContent}
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

// Image Preview
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $(previewId).attr('src', e.target.result).show();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Debounce Function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Format Currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
}

// Format Date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Get Status Badge HTML
function getStatusBadge(status) {
    const badges = {
        'Pending': 'badge-pending',
        'Approved': 'badge-approved',
        'Completed': 'badge-completed',
        'Rejected': 'badge-rejected',
        'Validated': 'badge-validated',
        'Invalid': 'badge-invalid'
    };
    
    const className = badges[status] || 'badge-pending';
    return `<span class="badge ${className}">${status}</span>`;
}

// Make functions global
window.showToast = showToast;
window.modalAlert = modalAlert;
window.confirmDialog = confirmDialog;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.submitFormAjax = submitFormAjax;
window.exportTableToCSV = exportTableToCSV;
window.printElement = printElement;
window.previewImage = previewImage;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.getStatusBadge = getStatusBadge;