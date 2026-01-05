let currentProduct = null;
let maxStock = 10;

function formatPrice(price) {
    return 'Rp ' + price.toLocaleString('id-ID');
}

function loadProductDetail() {
    currentProduct = window.currentProductData;
    if (!currentProduct) {
        console.error("No product data found for current page.");
        return;
    }

    maxStock = currentProduct.stock || 10;

    const maxQtyElement = document.getElementById('maxQty');
    if (maxQtyElement) {
        maxQtyElement.textContent = `Maks. ${maxStock} unit`;
    }
    const qtyInputElement = document.getElementById('quantity');
    if (qtyInputElement) {
        qtyInputElement.setAttribute('max', maxStock);
    }

}

function loadRelatedProducts() {
    const grid = document.getElementById('relatedProductsGrid');
    if(!grid) return;
    grid.innerHTML = '';

    const allProducts = window.allProductsData || [];
    const relatedProducts = allProducts
        .filter(p => currentProduct && p.category === currentProduct.category && p.id !== currentProduct.id)
        .slice(0, 3);

    relatedProducts.forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.onclick = () => {
            window.location.href = `product-detail.php?id=${product.id}`;
        };
        card.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-card-image">
            <div class="product-card-info">
                <div class="product-card-name">${product.name}</div>
                <div class="product-card-category">${product.category}</div>
                <div class="product-card-price">${formatPrice(product.price)}</div>
            </div>
        `;
        grid.appendChild(card);
    });
}

function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    let qty = parseInt(qtyInput.value);
    if (qty > 1) {
        qtyInput.value = qty - 1;
    }
}

function increaseQty() {
    const qtyInput = document.getElementById('quantity');
    let qty = parseInt(qtyInput.value);
    if (qty < maxStock) {
        qtyInput.value = qty + 1;
    }
}

function addToCart() {
    const qtyInput = document.getElementById('quantity');
    const qty = parseInt(qtyInput ? qtyInput.value : 1);

    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    const existingItem = cart.find(item => item.id === currentProduct.id);
    
    if (existingItem) {
        existingItem.quantity += qty;
    } else {
        cart.push({
            id: currentProduct.id,
            name: currentProduct.name,
            category: currentProduct.category,
            price: currentProduct.price,
            image: currentProduct.image,
            quantity: qty
        });
    }

    localStorage.setItem('cart', JSON.stringify(cart));

    updateCartCount();
    
    alert(`${currentProduct.name} (${qty} unit) telah ditambahkan ke keranjang!`);
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCount = document.getElementById('cartCountNav'); 
    if(cartCount) {
        cartCount.textContent = totalItems;
    }
}

window.addEventListener('load', function() {
    loadProductDetail();
    loadRelatedProducts();
    updateCartCount();
});
