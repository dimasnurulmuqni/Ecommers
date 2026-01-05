<?php 
include 'includes/header.php';
require_once 'includes/product_functions.php'; 

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'order-detail.php?id=' . ($_GET['id'] ?? '');
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'] ?? null;
$order = null;
$order_items = [];
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

$order = getOrderById($order_id);

if (!$order || $order['user_id'] !== $user_id) {
    echo "<div class='main-container'><p>Pesanan tidak ditemukan atau Anda tidak memiliki akses.</p></div>";
    include 'includes/footer.php';
    exit();
}

$order_items = getOrderItemsByOrderId($order_id);

?>

    <div class="back-button">
        <a href="orders.php" class="back-btn">
            ← Kembali ke Pesanan Saya
        </a>
    </div>

    <div class="main-container">
        <div>
            <div class="detail-section">
                <h2 class="section-title">Detail Pesanan</h2>
                
                <div class="order-header">
                    <div>
                        <div class="order-id" id="orderId">#<?php echo htmlspecialchars($order['id']); ?></div>
                        <div class="order-date" id="orderDate"><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></div>
                    </div>
                    <span class="status-badge badge-<?php echo htmlspecialchars($order['status']); ?>" id="orderStatus"><?php echo htmlspecialchars($order['status']); ?></span>
                </div>

                <div class="product-info" id="productInfo">
                    <?php foreach ($order_items as $item): ?>
                        <?php 
                            $product_detail = getProductById($item['product_id']);
                            $item_image_path = (!empty($product_detail) && !empty($product_detail['images'][0])) ? $product_detail['images'][0] : 'assets/images/placeholder.jpg';
                        ?>
                        <div class="product-entry">
                            <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $item_image_path); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-thumb">
                            <div class="product-details">
                                <div class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="product-qty">Jumlah: <?php echo htmlspecialchars($item['quantity']); ?></div>
                                <div class="product-price">Rp <?php echo number_format($item['price_at_order'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="info-row">
                    <span class="info-label">Alamat Pengiriman</span>
                    <span class="info-value" id="shippingAddress"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Pembayaran</span>
                    <span class="info-value" id="paymentMethod"><?php echo htmlspecialchars($order['payment_method'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Pengiriman</span>
                    <span class="info-value" id="shippingMethod"><?php echo htmlspecialchars($order['shipping_method'] ?? ''); ?></span>
                </div>
                <?php if (!empty($order['tracking_number'])): ?>
                <div class="info-row">
                    <span class="info-label">No. Resi</span>
                    <span class="info-value" id="trackingNumber"><?php echo htmlspecialchars($order['tracking_number']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($order['status'] == 'cancelled' && !empty($order['rejection_reason'])): ?>
                <div class="info-row" style="background-color: #fef2f2; padding: 10px; border-radius: 8px; margin-top: 15px;">
                    <span class="info-label" style="color: #dc2626; font-weight: 600;">Alasan Ditolak</span>
                    <span class="info-value" style="color: #dc2626;"><?php echo nl2br(htmlspecialchars($order['rejection_reason'])); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="tracking-section">
                <h2 class="section-title">Status Pengiriman</h2>
                <div class="timeline" id="timeline">
                    <div class="timeline-item active">
                        <div class="timeline-icon"><img src="assets/icons/ic_box.svg"/></div>
                        <div class="timeline-content">
                            <h3>Pengecekan Pesanan</h3>
                            <p><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></p>
                        </div>
                    </div>
                    <?php if ($order['status'] == 'paid' || $order['status'] == 'shipped' || $order['status'] == 'delivered'): ?>
                    <div class="timeline-item <?php echo ($order['status'] == 'paid' || $order['status'] == 'shipped' || $order['status'] == 'delivered') ? 'active' : ''; ?>">
                        <div class="timeline-icon"><img src="assets/icons/ic_nice_payment.svg"/></div>
                        <div class="timeline-content">
                            <h3>Pembayaran Dikonfirmasi</h3>
                            <p>Pembayaran pesanan Anda telah dikonfirmasi.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['status'] == 'shipped' || $order['status'] == 'delivered'): ?>
                    <div class="timeline-item <?php echo ($order['status'] == 'shipped' || $order['status'] == 'delivered') ? 'active' : ''; ?>">
                        <div class="timeline-icon"><img src="assets/icons/ic_shipping.svg"/></div>
                        <div class="timeline-content">
                            <h3>Pesanan Dikirim</h3>
                            <p>Pesanan Anda telah dikirim. <?php echo !empty($order['tracking_number']) ? 'No. Resi: ' . htmlspecialchars($order['tracking_number']) : ''; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['status'] == 'delivered'): ?>
                    <div class="timeline-item <?php echo ($order['status'] == 'delivered') ? 'active' : ''; ?>">
                        <div class="timeline-icon"><img src="assets/icons/ic_home.svg"/></div>
                        <div class="timeline-content">
                            <h3>Pesanan Diterima</h3>
                            <p>Pesanan Anda telah berhasil diterima.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <div class="summary-section">
                <h2 class="section-title">Ringkasan Pesanan</h2>
                
                <div class="summary-row">
                    <span>Subtotal Produk</span>
                    <span>Rp <?php echo number_format($order['total_amount'] - ($order['shipping_cost'] ?? 0), 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim (<?php echo htmlspecialchars($order['shipping_method'] ?? ''); ?>)</span>
                    <span>Rp <?php echo number_format($order['shipping_cost'] ?? 0, 0, ',', '.'); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Keseluruhan</span>
                    <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                </div>

                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="chatAdmin()">Chat Admin</button>
                    <a href="products.php" class="btn btn-secondary">Belanja Lagi</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chatAdmin() {
            const adminPhoneNumber = '6281234567890'; // Ganti dengan nomor WhatsApp admin Anda
            const orderIdElement = document.getElementById('orderId');
            const orderId = orderIdElement ? orderIdElement.textContent.trim() : 'ID tidak ditemukan';
            
            const message = `Halo Admin, saya ingin bertanya tentang pesanan saya dengan ID: ${orderId}`;
            const whatsappUrl = `https://wa.me/${adminPhoneNumber}?text=${encodeURIComponent(message)}`;
            
            window.open(whatsappUrl, '_blank');
        }
    </script>

    <script src="js/index.js"></script>

<?php include 'includes/footer.php'; ?>