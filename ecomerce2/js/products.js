function formatPrice(price) {
    return 'Rp ' + price.toLocaleString('id-ID');
}

function addToCart(productData) { // Menerima objek produk lengkap
    const productId = productData.id;
    
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productData.id,
            name: productData.name,
            category: productData.category_name, // Menggunakan category_name dari DB
            price: productData.price,
            image: productData.images[0] || 'assets/images/placeholder.jpg', // Gambar utama
            quantity: 1
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    alert(`${productData.name} telah ditambahkan ke keranjang!`);
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountNav = document.getElementById('cartCountNav');
    if(cartCountNav) {
        cartCountNav.textContent = totalItems;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});