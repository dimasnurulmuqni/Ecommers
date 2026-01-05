document.addEventListener('DOMContentLoaded', function () {


    const buyNowOrder = JSON.parse(localStorage.getItem('buyNowOrder'));
    const isBuyNow = Array.isArray(buyNowOrder) && buyNowOrder.length > 0;

    let checkoutItems = isBuyNow
        ? buyNowOrder
        : JSON.parse(localStorage.getItem('cart')) || [];

    console.log('=== CHECKOUT DEBUG ===');
    console.log('isBuyNow:', isBuyNow);
    console.log('buyNowOrder:', buyNowOrder);
    console.log('cart from localStorage:', JSON.parse(localStorage.getItem('cart')));
    console.log('checkoutItems:', checkoutItems);
    console.log('checkoutItems.length:', checkoutItems.length);

    if (checkoutItems.length === 0) {
        alert("Keranjang Anda kosong.");
        window.location.href = 'products.php';
        return;
    }

    const orderItemsContainer = document.getElementById('orderItems');
    const subtotalPriceEl = document.getElementById('subtotalPrice');
    const shippingPriceEl = document.getElementById('shippingPrice');
    const totalPriceEl = document.getElementById('totalPrice');

    const shippingMethodInput = document.getElementById('shipping_method');
    const shippingCostInput = document.getElementById('shipping_cost');
    const paymentMethodInput = document.getElementById('payment_method');
    const cartItemsInput = document.getElementById('cart_items');

    const shippingOptions = document.querySelectorAll('#shippingOptions .option-card');
    const paymentOptions = document.querySelectorAll('#paymentOptions .option-card');

    const paymentInstruction = document.getElementById('payment-instruction');
    const uploadInput = document.getElementById('upload');

    let subtotal = 0;
    let selectedShippingCost = 0;

    const formatCurrency = amount =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);

    function renderOrderSummary() {
        orderItemsContainer.innerHTML = '';
        subtotal = 0;

        checkoutItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            const el = document.createElement('div');
            el.className = 'summary-item';
            el.innerHTML = `
                <div class="item-info">
                    <span class="item-name">${item.name} (x${item.quantity})</span>
                </div>
                <span class="item-price">${formatCurrency(itemTotal)}</span>
            `;
            orderItemsContainer.appendChild(el);
        });

        updatePrices();
    }

    function updatePrices() {
        subtotalPriceEl.textContent = formatCurrency(subtotal);
        shippingPriceEl.textContent = formatCurrency(selectedShippingCost);
        totalPriceEl.textContent = formatCurrency(subtotal + selectedShippingCost);
    }

    shippingOptions.forEach(option => {
        option.addEventListener('click', () => {
            shippingOptions.forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');

            selectedShippingCost = parseInt(option.dataset.shippingCost);
            shippingMethodInput.value = option.dataset.shippingMethod;
            shippingCostInput.value = selectedShippingCost;

            updatePrices();
        });
    });

    paymentOptions.forEach(option => {
        option.addEventListener('click', () => {
            paymentOptions.forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');

            paymentMethodInput.value = option.dataset.paymentMethod;

            if (paymentMethodInput.value === 'transfer') {
                paymentInstruction.classList.remove('hidden');
                uploadInput.required = true;
            } else {
                paymentInstruction.classList.add('hidden');
                uploadInput.required = false;
            }
        });
    });

    const checkoutForm = document.getElementById('checkoutForm');

    checkoutForm.addEventListener('submit', function (e) {

        cartItemsInput.value = JSON.stringify(checkoutItems);

        if (!shippingMethodInput.value) {
            e.preventDefault();
            alert('Pilih metode pengiriman.');
            goToStep(3);
            return;
        }

        if (!paymentMethodInput.value) {
            e.preventDefault();
            alert('Pilih metode pembayaran.');
            goToStep(4);
            return;
        }

        if (paymentMethodInput.value === 'transfer' && uploadInput.files.length === 0) {
            e.preventDefault();
            alert('Upload bukti pembayaran.');
            goToStep(4);
            return;
        }
    });

    renderOrderSummary();
    if (shippingOptions.length) shippingOptions[0].click();
    if (paymentOptions.length) paymentOptions[0].click();
    goToStep(1);
});


function goToStep(step) {
    for (let i = 1; i <= 4; i++) {
        document.getElementById(`step${i}Content`).classList.add('hidden');
        document.getElementById(`stepIndicator${i}`).classList.remove('active', 'completed');
    }

    document.getElementById(`step${step}Content`).classList.remove('hidden');
    document.getElementById(`stepIndicator${step}`).classList.add('active');

    for (let i = 1; i < step; i++) {
        document.getElementById(`stepIndicator${i}`).classList.add('completed');
    }
}

window.validateStep2 = function () {
    const fullName = document.getElementById('fullName').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const city = document.getElementById('city').value.trim();
    const postalCode = document.getElementById('postalCode').value.trim();

    if (!fullName || !phone || !address || !city || !postalCode) {
        alert('Semua kolom alamat wajib diisi.');
        return;
    }

    if (!/^08\d{8,12}$/.test(phone)) {
        alert('Nomor HP tidak valid.');
        return;
    }

    if (!/^\d{5}$/.test(postalCode)) {
        alert('Kode pos harus 5 digit.');
        return;
    }

    goToStep(3);
};
