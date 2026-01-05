function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartBadge = document.getElementById('cartCount');
    if (cartBadge) {
        cartBadge.textContent = totalItems;
    }
    const cartCountNav = document.getElementById('cartCountNav');
    if (cartCountNav) {
        cartCountNav.textContent = totalItems;
    }
}

window.addEventListener('load', updateCartCount);
