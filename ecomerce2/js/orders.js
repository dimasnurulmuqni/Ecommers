function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountNav = document.getElementById('cartCountNav');
    if (cartCountNav) {
        cartCountNav.textContent = totalItems;
    }
}

function filterOrders(status) {
    window.location.href = `orders.php?status=${status}`;
}

window.addEventListener('load', function() {
    updateCartCount();
});
id: "ORD-2024-001",
        productId: 1,
        productName: "Laptop Gaming ROG",
        quantity: 1,
        price: 15000000,
        image: "https://images.unsplash.com/photo-1603481588273-2f908a9a7a1b?w=400&h=300&fit=crop",
        status: "delivered",
        date: "2024-12-15",
        shippingAddress: "Jl. Sudirman No. 123, Jakarta Pusat",
        paymentMethod: "Transfer Bank BCA"
    },
    {
        id: "ORD-2024-002",
        productId: 2,
        productName: "Smartphone Android Flagship",
        quantity: 2,
        price: 8500000,
        image: "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=300&fit=crop",
        status: "shipped",
        date: "2024-12-18",
        shippingAddress: "Jl. Gatot Subroto No. 45, Jakarta Selatan",
        paymentMethod: "Transfer Bank Mandiri"
    },
    {
        id: "ORD-2024-003",
        productId: 5,
        productName: "Smart Watch Fitness Tracker",
        quantity: 1,
        price: 2500000,
        image: "https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=400&h=300&fit=crop",
        status: "paid",
        date: "2024-12-20",
        shippingAddress: "Jl. M.H. Thamrin No. 78, Jakarta Pusat",
        paymentMethod: "Transfer Bank BNI"
    },
    {
        id: "ORD-2024-004",
        productId: 3,
        productName: "Sepatu Sneakers Premium",
        quantity: 1,
        price: 1200000,
        image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=300&fit=crop",
        status: "pending",
        date: "2024-12-22",
        shippingAddress: "Jl. Asia Afrika No. 90, Bandung",
        paymentMethod: "Transfer Bank BCA"
    },
    {
        id: "ORD-2024-005",
        productId: 11,
        productName: "Headphone Wireless Premium",
        quantity: 1,
        price: 1800000,
        image: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop",
        status: "cancelled",
        date: "2024-12-10",
        shippingAddress: "Jl. Diponegoro No. 56, Surabaya",
        paymentMethod: "Transfer Bank Mandiri"
    }
];

let currentFilter = 'all';

function formatPrice(price) {
    return 'Rp ' + price.toLocaleString('id-ID');
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'Menunggu Pembayaran',
        'paid': 'Sudah Dibayar',
        'shipped': 'Sedang Dikirim',
        'delivered': 'Selesai',
        'cancelled': 'Dibatalkan'
    };
    return statusMap[status] || status;
}

function renderOrders(filter = 'all') {
    const container = document.getElementById('ordersContainer');
    if (!container) return;
    container.innerHTML = '';

    let filteredOrders = orders;
    if (filter !== 'all') {
        filteredOrders = orders.filter(order => order.status === filter);
    }

    if (filteredOrders.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <h3>Tidak Ada Pesanan</h3>
                <p>Anda belum memiliki pesanan dengan status ini</p>
                <a href="products.php" class="shop-now-btn">Belanja Sekarang</a>
            </div>
        `;
        return;
    }

    filteredOrders.forEach(order => {
        const totalPrice = order.price * order.quantity;
        const card = document.createElement('div');
        card.className = 'order-card';
        card.innerHTML = `
            <div class="order-header">
                <div>
                    <div class="order-id">${order.id}</div>
                    <div class="order-date">${formatDate(order.date)}</div>
                </div>
            </div>
            <div class="order-body">
                <img src="${order.image}" alt="${order.productName}" class="order-image">
                <div class="order-info">
                    <div class="order-product-name">${order.productName}</div>
                    <div class="order-quantity">Jumlah: ${order.quantity} unit</div>
                    <div class="order-price">${formatPrice(totalPrice)}</div>
                </div>
            </div>
            <div class="order-footer">
                <span class="status-badge status-${order.status}">${getStatusText(order.status)}</span>
                <a href="order-detail.php?id=${order.id}" class="view-detail-btn">Lihat Detail</a>
            </div>
        `;
        container.appendChild(card);
    });

    const orderCountElement = document.getElementById('orderCount');
    if (orderCountElement) {
        orderCountElement.textContent = 
        `Menampilkan ${filteredOrders.length} pesanan`;
    }
}

function filterOrders(status) {
    currentFilter = status;

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    const buttons = document.querySelectorAll('.tab-btn');
    for (let i = 0; i < buttons.length; i++) {
        const buttonText = buttons[i].textContent.toLowerCase().trim();
        const statusLower = status.toLowerCase().trim();
        if ((statusLower === 'all' && buttonText === 'semua') || (buttonText.includes(statusLower) && statusLower !== 'all')) {
            buttons[i].classList.add('active');
            break;
        }
    }

    renderOrders(status);
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountNav = document.getElementById('cartCountNav'); 
    if (cartCountNav) {
        cartCountNav.textContent = totalItems;
    }
}

localStorage.setItem('orders', JSON.stringify(orders));

window.addEventListener('load', function() {
    renderOrders();
    updateCartCount();
});
