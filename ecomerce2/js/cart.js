function formatPrice(price) {
    return 'Rp ' + price.toLocaleString('id-ID');
}

function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartItemsContainer = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const summaryItemsContainer = document.getElementById('summaryItems');

    if (cart.length === 0) {
        if(cartItemsContainer) cartItemsContainer.style.display = 'none';
        if(emptyCart) emptyCart.style.display = 'block';
        const cartActions = document.querySelector('.cart-actions');
        if(cartActions) cartActions.style.display = 'none';
        const cartSummary = document.querySelector('.cart-summary');
        if(cartSummary) cartSummary.style.display = 'none';
    } else {
        if(cartItemsContainer) cartItemsContainer.style.display = 'flex';
        if(emptyCart) emptyCart.style.display = 'none';
        const cartActions = document.querySelector('.cart-actions');
        if(cartActions) cartActions.style.display = 'flex';
        const cartSummary = document.querySelector('.cart-summary');
        if(cartSummary) cartSummary.style.display = 'block';

        if(cartItemsContainer) cartItemsContainer.innerHTML = '';
        if(summaryItemsContainer) summaryItemsContainer.innerHTML = '';

        cart.forEach((item, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'cart-item';
            itemDiv.innerHTML = `
                <img src="${item.image}" alt="${item.name}" class="item-image">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-category">${item.category || ''}</div>
                    <div class="item-price">${formatPrice(item.price)}</div>
                </div>
                <div class="item-actions">
                    <button class="remove-btn" onclick="removeItem(${index})" title="Hapus"><img src="assets/icons/ic_trash.svg"/></button>
                    <div class="quantity-control">
                        <button class="qty-btn" onclick="updateQuantity(${index}, -1)">−</button>
                        <input type="number" class="qty-input" value="${item.quantity}" readonly>
                        <button class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                    </div>
                </div>
            `;
            if(cartItemsContainer) cartItemsContainer.appendChild(itemDiv);

            const summaryItem = document.createElement('div');
            summaryItem.className = 'summary-item';
            summaryItem.innerHTML = `
                <span>${item.name} (x${item.quantity})</span>
                <span>${formatPrice(item.price * item.quantity)}</span>
            `;
            if(summaryItemsContainer) summaryItemsContainer.appendChild(summaryItem);
        });
    }

    updateCartCount();
    updateTotal();
}

function updateQuantity(index, change) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (cart[index]) {
        cart[index].quantity += change;
        
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }
}

function removeItem(index) {
    if (confirm('Hapus produk ini dari keranjang?')) {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCart();
    }
}

function clearCart() {
    if (confirm('Kosongkan semua produk dari keranjang?')) {
        localStorage.removeItem('cart');
        loadCart();
    }
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    const uniqueItemCount = cart.length;
    
    const cartCountNav = document.getElementById('cartCountNav');
    if(cartCountNav) {
        cartCountNav.textContent = totalQuantity;
    }
    
    const cartItemCount = document.getElementById('cartItemCount');
    if(cartItemCount) {
        if (uniqueItemCount === 0) {
            cartItemCount.textContent = '0 produk dalam keranjang';
        } else if (uniqueItemCount === 1) {
            cartItemCount.textContent = '1 jenis produk dalam keranjang';
        } else {
            cartItemCount.textContent = `${uniqueItemCount} jenis produk dalam keranjang`;
        }
    }
}

function updateTotal() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const totalPrice = document.getElementById('totalPrice');

    if(totalPrice) {
        totalPrice.textContent = formatPrice(total);
    }
}

function checkout() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    if (cart.length === 0) {
        alert('Keranjang belanja Anda kosong!');
        return;
    }

    window.location.href = 'checkout.php';
}

window.addEventListener('load', function() {
    const body = document.querySelector('body');
    const invalidIdsAttr = body.getAttribute('data-invalid-ids');

    if (invalidIdsAttr) {
        const invalidIds = JSON.parse(invalidIdsAttr);
        if (invalidIds && invalidIds.length > 0) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            const initialCount = cart.length;
            cart = cart.filter(item => !invalidIds.includes(item.id));
            const finalCount = cart.length;

            if (initialCount > finalCount) {
                localStorage.setItem('cart', JSON.stringify(cart));
            }

            body.removeAttribute('data-invalid-ids');
        }
    }

    loadCart();
});
