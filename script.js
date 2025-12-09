// script.js - FULL CODE YANG SUDAH DIPERBAIKI
class RestaurantApp {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        this.user = JSON.parse(localStorage.getItem('user') || 'null');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupMenuFilters();
        this.updateCartUI();
        this.checkAuthStatus();
    }

    setupEventListeners() {
        // Modal handlers
        if (document.getElementById('login-btn')) {
            document.getElementById('login-btn').addEventListener('click', () => this.showModal('login-modal'));
        }
        if (document.getElementById('register-btn')) {
            document.getElementById('register-btn').addEventListener('click', () => this.showModal('register-modal'));
        }
        
        // Close modal buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => this.hideModals());
        });

        // Cart handlers
        const cartIcon = document.querySelector('.fa-shopping-cart');
        if (cartIcon) {
            cartIcon.addEventListener('click', () => this.showCart());
        }

        const checkoutBtn = document.getElementById('checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => this.checkout());
        }

        // Add to cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.add-to-cart')) {
                const btn = e.target.closest('.add-to-cart');
                const item = {
                    id: parseInt(btn.dataset.id),
                    name: btn.dataset.name,
                    price: parseFloat(btn.dataset.price),
                    quantity: 1
                };
                this.addToCart(item);
            }
        });

        // Close modals when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.hideModals();
            }
        });

        // Auth form submissions
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        const registerForm = document.getElementById('register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.handleRegister(e));
        }

        // Order notes input
        const orderNotes = document.getElementById('order-notes');
        if (orderNotes) {
            orderNotes.addEventListener('input', (e) => {
                localStorage.setItem('order_notes', e.target.value);
            });
            
            // Load saved notes
            const savedNotes = localStorage.getItem('order_notes');
            if (savedNotes) {
                orderNotes.value = savedNotes;
            }
        }
    }

    showModal(modalId) {
        document.getElementById(modalId).classList.add('show');
    }

    hideModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('show');
        });
    }

    addToCart(item) {
        const existingItem = this.cart.find(cartItem => cartItem.id === item.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push(item);
        }
        
        this.saveCart();
        this.updateCartUI();
        this.showNotification(`${item.name} ditambahkan ke keranjang`, 'success');
    }

    saveCart() {
        localStorage.setItem('cart', JSON.stringify(this.cart));
    }

    updateCartUI() {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
        }
        
        if (document.getElementById('cart-modal')?.classList.contains('show')) {
            this.renderCart();
        }
    }

    renderCart() {
        const cartItems = document.querySelector('.cart-items');
        const cartTotal = document.querySelector('.cart-total');
        
        if (!cartItems || !cartTotal) return;
        
        if (this.cart.length === 0) {
            cartItems.innerHTML = '<p class="empty-cart">Keranjang kosong</p>';
            cartTotal.textContent = 'Total: Rp 0';
            return;
        }
        
        cartItems.innerHTML = this.cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p>Rp ${item.price.toLocaleString()} x ${item.quantity}</p>
                </div>
                <div class="cart-item-actions">
                    <button class="quantity-btn" data-id="${item.id}" data-action="decrease">-</button>
                    <span>${item.quantity}</span>
                    <button class="quantity-btn" data-id="${item.id}" data-action="increase">+</button>
                    <button class="quantity-btn remove-btn" data-id="${item.id}" data-action="remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
        
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = `Total: Rp ${total.toLocaleString()}`;
        
        // Add event listeners to cart buttons
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const itemId = parseInt(btn.dataset.id);
                const action = btn.dataset.action;
                this.updateCartItem(itemId, action);
            });
        });
    }

    updateCartItem(itemId, action) {
        const itemIndex = this.cart.findIndex(item => item.id === itemId);
        
        if (itemIndex === -1) return;
        
        switch (action) {
            case 'increase':
                this.cart[itemIndex].quantity += 1;
                break;
            case 'decrease':
                if (this.cart[itemIndex].quantity > 1) {
                    this.cart[itemIndex].quantity -= 1;
                } else {
                    this.cart.splice(itemIndex, 1);
                }
                break;
            case 'remove':
                this.cart.splice(itemIndex, 1);
                break;
        }
        
        this.saveCart();
        this.updateCartUI();
    }

    showCart() {
        this.renderCart();
        this.showModal('cart-modal');
    }

    async checkout() {
    if (!this.user) {
        this.showNotification('Silakan login terlebih dahulu untuk memesan', 'error');
        this.hideModals();
        setTimeout(() => this.showModal('login-modal'), 300);
        return;
    }

    if (this.cart.length === 0) {
        this.showNotification('Keranjang Anda kosong', 'error');
        return;
    }

    const notes = document.getElementById('order-notes')?.value || '';

    try {
        // Tampilkan loading
        this.showNotification('Memproses pesanan...', 'info');

        const formData = new URLSearchParams();
        formData.append('notes', notes);
        formData.append('payment_method', 'transfer');
        formData.append('cart_items', JSON.stringify(this.cart));

        const response = await fetch('process-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            this.showNotification(data.message, 'success');
            
            // Kosongkan keranjang
            this.cart = [];
            this.saveCart();
            this.updateCartUI();
            this.hideModals();
            
            // Clear saved notes
            localStorage.removeItem('order_notes');
            
            // Redirect ke halaman upload pembayaran
            setTimeout(() => {
                window.location.href = data.redirect_url || 'upload-payment.php?order_id=' + data.order_id;
            }, 1500);
        } else {
            this.showNotification(data.message, 'error');
        }
    } catch (error) {
        console.error('Checkout error:', error);
        this.showNotification('Terjadi kesalahan saat memproses pesanan', 'error');
    }
}
    async handleLogin(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('auth.php?action=login', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.user = data.user;
                localStorage.setItem('user', JSON.stringify(data.user));
                this.showNotification('Login berhasil!', 'success');
                this.hideModals();
                this.updateAuthUI();
                
                // Refresh page untuk update UI
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            this.showNotification('Terjadi kesalahan saat login', 'error');
        }
    }

    async handleRegister(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('auth.php?action=register', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Registrasi berhasil! Silakan login.', 'success');
                this.hideModals();
                setTimeout(() => this.showModal('login-modal'), 300);
            } else {
                this.showNotification(data.message, 'error');
            }
        } catch (error) {
            this.showNotification('Terjadi kesalahan saat registrasi', 'error');
        }
    }

    checkAuthStatus() {
        if (this.user) {
            this.updateAuthUI();
        }
    }

    updateAuthUI() {
        const loginBtn = document.getElementById('login-btn');
        const registerBtn = document.getElementById('register-btn');
        const userInfo = document.getElementById('user-info');
        
        if (this.user && userInfo) {
            userInfo.innerHTML = `
                <span>Halo, ${this.user.name}</span>
                <a href="dashboard.php" class="btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            `;
            
            if (loginBtn) loginBtn.style.display = 'none';
            if (registerBtn) registerBtn.style.display = 'none';
        }
    }

    showNotification(message, type = 'info') {
        // Hapus notifikasi sebelumnya
        const existingNotif = document.querySelector('.notification');
        if (existingNotif) {
            existingNotif.remove();
        }

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove setelah 5 detik
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    setupMenuFilters() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const menuItems = document.querySelectorAll('.menu-item');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;

                menuItems.forEach(item => {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new RestaurantApp();
});

// Add CSS for notifications and animations
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        min-width: 300px;
        max-width: 500px;
        color: white;
        font-weight: 500;
    }
    
    .notification.success {
        background: #28a745;
    }
    
    .notification.error {
        background: #dc3545;
    }
    
    .notification.info {
        background: #17a2b8;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    
    .notification-content i {
        font-size: 1.2rem;
    }
    
    .empty-cart {
        text-align: center;
        padding: 2rem;
        color: #666;
        font-style: italic;
    }
    
    .cart-item {
        transition: all 0.3s ease;
    }
    
    .menu-item {
        transition: all 0.3s ease;
    }
    
    @keyframes slideIn {
        from { 
            transform: translateX(100%); 
            opacity: 0; 
        }
        to { 
            transform: translateX(0); 
            opacity: 1; 
        }
    }
    
    @keyframes fadeOut {
        from { 
            transform: translateX(0); 
            opacity: 1; 
        }
        to { 
            transform: translateX(100%); 
            opacity: 0; 
        }
    }
`;
document.head.appendChild(style);

// Modal functionality
const modals = document.querySelectorAll('.modal');
const loginBtn = document.getElementById('login-btn');
const registerBtn = document.getElementById('register-btn');
const cartIcon = document.querySelector('.cart-container');
const closeModal = document.querySelectorAll('.close-modal');

// Open modals
loginBtn?.addEventListener('click', () => openModal('login-modal'));
registerBtn?.addEventListener('click', () => openModal('register-modal'));
cartIcon?.addEventListener('click', () => {
    if (!isLoggedIn()) {
        openModal('login-modal');
        return;
    }
    openModal('cart-modal');
    loadCart();
});

// Close modals
closeModal.forEach(btn => {
    btn.addEventListener('click', () => {
        modals.forEach(modal => modal.style.display = 'none');
    });
});

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    modals.forEach(modal => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

function openModal(modalId) {
    modals.forEach(modal => modal.style.display = 'none');
    document.getElementById(modalId).style.display = 'flex';
}

// Check if user is logged in
function isLoggedIn() {
    return document.querySelector('.user-menu') !== null;
}

// Login form
document.getElementById('login-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat login');
    }
});

// Register form
document.getElementById('register-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat registrasi');
    }
});

// Add to cart functionality
document.querySelectorAll('.add-to-cart').forEach(btn => {
    btn.addEventListener('click', async (e) => {
        if (!isLoggedIn()) {
            openModal('login-modal');
            return;
        }
        
        const menuId = e.currentTarget.dataset.id;
        const menuName = e.currentTarget.dataset.name;
        
        try {
            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('menu_id', menuId);
            formData.append('quantity', 1);
            
            const response = await fetch('cart.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success animation
                const cartCount = document.querySelector('.cart-count');
                cartCount.textContent = parseInt(cartCount.textContent) + 1;
                cartCount.style.animation = 'bounce 0.5s';
                
                setTimeout(() => {
                    cartCount.style.animation = '';
                }, 500);
                
                // Show success message
                showNotification(menuName + ' berhasil ditambahkan ke keranjang!');
            } else {
                alert(result.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan saat menambahkan ke keranjang');
        }
    });
});

// Load cart items
async function loadCart() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_cart');
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            document.querySelector('.cart-items').innerHTML = result.html;
            document.querySelector('.cart-total').textContent = 'Total: Rp ' + result.total.toLocaleString('id-ID');
            document.querySelector('.cart-count').textContent = result.count;
            
            // Add event listeners to cart items
            addCartEventListeners();
        }
    } catch (error) {
        console.error('Error loading cart:', error);
    }
}

function addCartEventListeners() {
    // Quantity buttons
    document.querySelectorAll('.btn-quantity').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const cartId = e.currentTarget.dataset.id;
            const isPlus = e.currentTarget.classList.contains('plus');
            const change = isPlus ? 1 : -1;
            
            try {
                const formData = new FormData();
                formData.append('action', 'update_quantity');
                formData.append('cart_id', cartId);
                formData.append('change', change);
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadCart(); // Reload cart
                }
            } catch (error) {
                console.error('Error updating quantity:', error);
            }
        });
    });
    
    // Remove buttons
    document.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const cartId = e.currentTarget.dataset.id;
            
            try {
                const formData = new FormData();
                formData.append('action', 'remove_item');
                formData.append('cart_id', cartId);
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadCart(); // Reload cart
                }
            } catch (error) {
                console.error('Error removing item:', error);
            }
        });
    });
}

// Checkout functionality
document.getElementById('checkout-btn')?.addEventListener('click', async () => {
    if (!isLoggedIn()) {
        openModal('login-modal');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'get_cart');
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success && result.count > 0) {
            // Show payment modal
            openModal('payment-modal');
            
            // Update payment summary
            document.getElementById('payment-order-items').innerHTML = result.html;
            document.getElementById('payment-total-amount').textContent = 'Rp ' + result.total.toLocaleString('id-ID');
            document.getElementById('transfer-amount').textContent = 'Rp ' + result.total.toLocaleString('id-ID');
        } else {
            alert('Keranjang kosong!');
        }
    } catch (error) {
        console.error('Error during checkout:', error);
    }
});

// Payment method change
document.getElementById('payment-method')?.addEventListener('change', (e) => {
    const method = e.target.value;
    
    // Hide all instructions
    document.querySelectorAll('.payment-instructions').forEach(el => {
        el.style.display = 'none';
    });
    
    // Show selected method instructions
    document.getElementById(method + '-instructions').style.display = 'block';
    
    // Show/hide proof upload section
    const proofSection = document.getElementById('proof-upload-section');
    proofSection.style.display = method === 'cash' ? 'none' : 'block';
});

// Payment form submission
document.getElementById('payment-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'process_payment');
    
    try {
        const response = await fetch('payment.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Pembayaran berhasil diproses!');
            // Close modals and reload
            modals.forEach(modal => modal.style.display = 'none');
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        alert('Terjadi kesalahan saat memproses pembayaran');
    }
});

// Utility function for notifications
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Menu filtering
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        const filter = btn.dataset.filter;
        const menuItems = document.querySelectorAll('.menu-item');
        
        menuItems.forEach(item => {
            if (filter === 'all' || item.dataset.category === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Testimonial slider
let currentSlide = 0;
const slides = document.querySelectorAll('.testimonial-slide');
const dots = document.querySelectorAll('.slider-dot');

function showSlide(n) {
    slides.forEach(slide => slide.style.display = 'none');
    dots.forEach(dot => dot.classList.remove('active'));
    
    currentSlide = (n + slides.length) % slides.length;
    slides[currentSlide].style.display = 'block';
    dots[currentSlide].classList.add('active');
}

dots.forEach((dot, index) => {
    dot.addEventListener('click', () => showSlide(index));
});

// Auto slide
setInterval(() => {
    showSlide(currentSlide + 1);
}, 5000);

// Initialize
showSlide(0);
// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize modals
    initModals();
    
    // Initialize cart
    initCart();
    
    // Initialize auth forms
    initAuthForms();
    
    // Load initial cart count
    updateCartCount();
}

function initModals() {
    const modals = document.querySelectorAll('.modal');
    const loginBtn = document.getElementById('login-btn');
    const registerBtn = document.getElementById('register-btn');
    const cartIcon = document.querySelector('.cart-container');
    const closeButtons = document.querySelectorAll('.close-modal');

    // Open modals
    if (loginBtn) {
        loginBtn.addEventListener('click', () => openModal('login-modal'));
    }
    
    if (registerBtn) {
        registerBtn.addEventListener('click', () => openModal('register-modal'));
    }
    
    if (cartIcon) {
        cartIcon.addEventListener('click', handleCartClick);
    }

    // Close modals
    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeAllModals();
        }
    });
}

function openModal(modalId) {
    closeAllModals();
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.style.display = 'none';
    });
    document.body.style.overflow = 'auto';
}

function handleCartClick() {
    if (!isLoggedIn()) {
        openModal('login-modal');
        showNotification('Silakan login terlebih dahulu untuk melihat keranjang!', 'warning');
        return;
    }
    openModal('cart-modal');
    loadCart();
}

function isLoggedIn() {
    return document.querySelector('.user-menu') !== null;
}

function initAuthForms() {
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    // Register form
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
}

async function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan saat login', 'error');
    } finally {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

async function handleRegister(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftarkan...';
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch('auth.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan saat registrasi', 'error');
    } finally {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Add to cart functionality dengan debugging
function initCart() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart')) {
            const btn = e.target.closest('.add-to-cart');
            console.log('Add to cart clicked:', btn.dataset);
            handleAddToCart(btn);
        }
    });
}

async function handleAddToCart(btn) {
    console.log('handleAddToCart called');
    
    if (!isLoggedIn()) {
        openModal('login-modal');
        showNotification('Silakan login terlebih dahulu untuk menambah item!', 'warning');
        return;
    }
    
    const menuId = btn.dataset.id;
    const menuName = btn.dataset.name;
    const menuPrice = btn.dataset.price;
    
    console.log('Adding to cart:', { menuId, menuName, menuPrice });
    
    // Add loading animation to button
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('menu_id', menuId);
        formData.append('quantity', 1);
        
        console.log('Sending request to cart.php...');
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response received:', response);
        
        const result = await response.json();
        console.log('Result from server:', result);
        
        if (result.success) {
            showNotification(`${menuName} berhasil ditambahkan ke keranjang!`, 'success');
            updateCartCount(result.cart_count);
            
            // Add animation to cart icon
            const cartIcon = document.querySelector('.cart-container');
            if (cartIcon) {
                cartIcon.classList.add('bounce');
                setTimeout(() => {
                    cartIcon.classList.remove('bounce');
                }, 600);
            }
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showNotification('Terjadi kesalahan saat menambahkan ke keranjang: ' + error.message, 'error');
    } finally {
        // Reset button
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
}

// Update cart count function
function updateCartCount(count) {
    console.log('Updating cart count:', count);
    
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'flex' : 'none';
    }
}

// Load initial cart count
async function loadInitialCartCount() {
    if (!isLoggedIn()) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'get_cart');
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            updateCartCount(result.count);
        }
    } catch (error) {
        console.error('Error loading cart count:', error);
    }
}

async function handleAddToCart(btn) {
    if (!isLoggedIn()) {
        openModal('login-modal');
        showNotification('Silakan login terlebih dahulu untuk menambah item!', 'warning');
        return;
    }
    
    const menuId = btn.dataset.id;
    const menuName = btn.dataset.name;
    
    // Add loading animation to button
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('menu_id', menuId);
        formData.append('quantity', 1);
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(`${menuName} berhasil ditambahkan ke keranjang!`, 'success');
            updateCartCount(result.cart_count);
            
            // Add animation to cart icon
            const cartIcon = document.querySelector('.cart-container');
            cartIcon.classList.add('bounce');
            setTimeout(() => {
                cartIcon.classList.remove('bounce');
            }, 600);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan saat menambahkan ke keranjang', 'error');
    } finally {
        // Reset button
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
}

async function loadCart() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_cart');
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            const cartItems = document.querySelector('.cart-items');
            const cartTotal = document.querySelector('.cart-total');
            
            if (cartItems) {
                cartItems.innerHTML = result.html;
            }
            
            if (cartTotal) {
                cartTotal.textContent = `Total: ${result.formatted_total}`;
            }
            
            // Update cart count in header
            updateCartCount(result.count);
            
            // Add event listeners to cart items
            attachCartEventListeners();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan memuat keranjang', 'error');
    }
}

function attachCartEventListeners() {
    // Quantity minus buttons
    document.querySelectorAll('.qty-btn.minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const cartId = this.dataset.id;
            const quantityElement = this.nextElementSibling;
            let quantity = parseInt(quantityElement.textContent);
            
            if (quantity > 1) {
                updateCartQuantity(cartId, quantity - 1);
            }
        });
    });
    
    // Quantity plus buttons
    document.querySelectorAll('.qty-btn.plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const cartId = this.dataset.id;
            const quantityElement = this.previousElementSibling;
            let quantity = parseInt(quantityElement.textContent);
            
            updateCartQuantity(cartId, quantity + 1);
        });
    });
    
    // Remove buttons
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const cartId = this.dataset.id;
            removeCartItem(cartId);
        });
    });
}

async function updateCartQuantity(cartId, newQuantity) {
    try {
        const formData = new FormData();
        formData.append('action', 'update_cart');
        formData.append('cart_id', cartId);
        formData.append('quantity', newQuantity);
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadCart(); // Reload cart to reflect changes
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan mengupdate keranjang', 'error');
    }
}

async function removeCartItem(cartId) {
    if (!confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'remove_item');
        formData.append('cart_id', cartId);
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            loadCart(); // Reload cart
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan menghapus item', 'error');
    }
}

function updateCartCount(count) {
    if (count === undefined) {
        // If no count provided, try to get it from server
        if (isLoggedIn()) {
            loadCart();
        }
        return;
    }
    
    const cartCount = document.querySelector('.cart-count');
    if (cartCount) {
        cartCount.textContent = count;
        cartCount.style.display = count > 0 ? 'flex' : 'none';
    }
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles if not already added
    if (!document.querySelector('#notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                z-index: 10000;
                animation: slideInRight 0.3s ease;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .notification-success { background: #4CAF50; }
            .notification-error { background: #f44336; }
            .notification-warning { background: #ff9800; }
            .notification-info { background: #2196F3; }
            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes bounce {
                0%, 20%, 60%, 100% { transform: translateY(0); }
                40% { transform: translateY(-10px); }
                80% { transform: translateY(-5px); }
            }
            .bounce { animation: bounce 0.6s; }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideInRight 0.3s ease reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 3000);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Checkout functionality
document.getElementById('checkout-btn')?.addEventListener('click', function() {
    if (!isLoggedIn()) {
        openModal('login-modal');
        return;
    }
    
    // For now, just show a message
    showNotification('Fitur checkout sedang dalam pengembangan!', 'info');
});

// Test if everything is working
console.log('TriyaskaFood App Initialized');
// Checkout functionality
async function processCheckout() {
    if (!isLoggedIn()) {
        openModal('login-modal');
        showNotification('Silakan login terlebih dahulu!', 'warning');
        return;
    }
    
    const notes = document.getElementById('order-notes')?.value || '';
    
    try {
        const formData = new FormData();
        formData.append('notes', notes);
        
        const response = await fetch('checkout.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeAllModals();
            
            // Show payment modal
            setTimeout(() => {
                openModal('payment-modal');
                document.getElementById('payment-order-id').value = result.order_id;
                document.getElementById('payment-total-amount').textContent = 'Rp ' + result.total_amount.toLocaleString('id-ID');
                document.getElementById('transfer-amount').textContent = 'Rp ' + result.total_amount.toLocaleString('id-ID');
            }, 1000);
            
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan saat checkout', 'error');
    }
}

// Payment functionality
async function processPayment(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('payment.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeAllModals();
            
            // Redirect to dashboard after 2 seconds
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);
            
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Terjadi kesalahan saat memproses pembayaran', 'error');
    }
}

// Update event listeners di function initializeApp()
function initializeApp() {
    initModals();
    initCart();
    initAuthForms();
    initMenuFilters();
    updateCartCount();
    
    // Add checkout event listener
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', processCheckout);
    }
    
    // Add payment form event listener
    const paymentForm = document.getElementById('payment-form');
    if (paymentForm) {
        paymentForm.addEventListener('submit', processPayment);
    }
}