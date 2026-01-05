<?php 
include 'includes/header.php';
require_once 'includes/product_functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'orders.php';
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? 'all';

$all_orders = getOrdersByUserId($user_id);
$filtered_orders = [];

if ($status_filter === 'all') {
    $filtered_orders = $all_orders;
} else {
    foreach ($all_orders as $order) {
        if ($order['status'] === $status_filter) {
            $filtered_orders[] = $order;
        }
    }
}
?>
    <div class="page-header">
        <h1>Pesanan Saya</h1>
        <p id="orderCount">Menampilkan <?php echo count($filtered_orders); ?> pesanan</p>
    </div>

    <div class="filter-tabs">
        <a href="?status=all" class="tab-btn <?php echo ($status_filter == 'all') ? 'active' : ''; ?>">Semua</a>
        <a href="?status=pending" class="tab-btn <?php echo ($status_filter == 'pending') ? 'active' : ''; ?>">Menunggu Pembayaran</a>
        <a href="?status=paid" class="tab-btn <?php echo ($status_filter == 'paid') ? 'active' : ''; ?>">Sudah Dibayar</a>
        <a href="?status=shipped" class="tab-btn <?php echo ($status_filter == 'shipped') ? 'active' : ''; ?>">Dikirim</a>
        <a href="?status=delivered" class="tab-btn <?php echo ($status_filter == 'delivered') ? 'active' : ''; ?>">Selesai</a>
        <a href="?status=cancelled" class="tab-btn <?php echo ($status_filter == 'cancelled') ? 'active' : ''; ?>">Dibatalkan</a>
    </div>

    <div class="orders-container" id="ordersContainer">
        <?php if (empty($filtered_orders)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open fa-4x"></i>
                <p>Belum ada pesanan dengan status ini.</p>
                <a href="products.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
        <?php else: ?>
            <?php foreach ($filtered_orders as $order): ?>
                <?php $order_items = getOrderItemsByOrderId($order['id']); ?>
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-id">#<?php echo htmlspecialchars($order['id']); ?></span>
                        <span class="order-date"><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></span>
                        <span class="order-status badge-<?php echo htmlspecialchars($order['status']); ?>"><?php echo htmlspecialchars($order['status']); ?></span>
                    </div>
                    <div class="order-body">
                        <?php foreach ($order_items as $item): ?>
                            <?php 
                                $product_detail = getProductById($item['product_id']);
                                $item_image_path = (!empty($product_detail) && !empty($product_detail['images'][0])) ? $product_detail['images'][0] : 'assets/images/placeholder.jpg';
                            ?>
                            <div class="order-item">
                                <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $item_image_path); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="item-image">
                                <div class="item-info">
                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div class="item-quantity"><?php echo htmlspecialchars($item['quantity']); ?> x Rp <?php echo number_format($item['price_at_order'], 0, ',', '.'); ?></div>
                                </div>
                                <div class="item-price">Rp <?php echo number_format($item['price_at_order'] * $item['quantity'], 0, ',', '.'); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="order-footer">
                        <span class="total-label">Total Pesanan:</span>
                        <span class="total-amount">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                        <a href="order-detail.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-detail">Lihat Detail</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const orderSuccess = urlParams.get('order_success');
    const source = urlParams.get('source');

    if (orderSuccess === 'true') {
        if (source === 'buyNow') {
            localStorage.removeItem('buyNowOrder');
            console.log('Item "Beli Sekarang" telah dihapus.');
        } else {
            localStorage.removeItem('cart');
            console.log('Keranjang telah dikosongkan karena pesanan berhasil.');
        }

        const cartCountNav = document.getElementById('cartCountNav');
        if (cartCountNav) {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCountNav.textContent = totalQuantity;
        }

        const newUrl = window.location.pathname + '?status=all';
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script>

<?php include 'includes/footer.php'; ?>