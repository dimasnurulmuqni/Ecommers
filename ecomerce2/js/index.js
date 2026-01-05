function searchProducts() {
    const searchTerm = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;

    window.location.href = `products.php?search=${encodeURIComponent(searchTerm)}&category=${encodeURIComponent(category)}`;
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountNav = document.getElementById('cartCountNav');
    if(cartCountNav) {
        cartCountNav.textContent = totalItems;
    }
}

function addToCart(product) {
    console.log("Produk ditambahkan:", product);
    const qtyInput = document.getElementById('quantity');
    const quantity = parseInt(qtyInput.value);

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    let existing = cart.find(item => item.id == product.id);

    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.images?.[0] ?? '',
            quantity: 1
        });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});


