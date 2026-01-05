function chatAdmin() {
    const adminPhone = '6285370508360';
    const message = 'Halo Admin, saya ingin bertanya tentang pesanan saya.';
    
    const url = `https://wa.me/${adminPhone}?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountNav = document.getElementById('cartCountNav');
    if(cartCountNav){
        cartCountNav.textContent = totalItems;
    }
}

window.addEventListener('load', function() {
    updateCartCount();
});
