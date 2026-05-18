</main>
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
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});
function showLoading(message = 'Loading...') {
    if (document.getElementById('loadingOverlay')) return;
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `<div class="spinner"></div><p class="text-white ml-3">${message}</p>`;
    document.body.appendChild(overlay);
}
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.remove();
}
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
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showToast = showToast;
</script>
</body>
</html>