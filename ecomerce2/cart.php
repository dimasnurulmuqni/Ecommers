<?php 
include 'includes/header.php'; 

$invalid_ids_json = '[]';
if (isset($_SESSION['invalid_product_ids']) && !empty($_SESSION['invalid_product_ids'])) {
    $invalid_ids_json = json_encode($_SESSION['invalid_product_ids']);
}
?>

<body data-invalid-ids='<?= htmlspecialchars($invalid_ids_json, ENT_QUOTES, 'UTF-8') ?>'>

    <div class="cart-container">
        <div>
            <?php
            if (isset($_SESSION['checkout_error'])) {
                echo '<div class="message error" style="margin-bottom: 20px; padding: 15px; background: #ffebee; color: #d32f2f; border-radius: 8px;">' . htmlspecialchars($_SESSION['checkout_error']) . '</div>';
                unset($_SESSION['checkout_error']);
                unset($_SESSION['invalid_product_ids']);
            }
            ?>
            <div class="cart-header">
                <h1>Keranjang Belanja</h1>
                <p id="cartItemCount">0 produk dalam keranjang</p>
            </div>

            <div class="cart-actions">
                <button class="clear-cart-btn" onclick="clearCart()">
                    <img class="ic-btn-clear" src="assets/icons/ic_trash.svg"/> Kosongkan Keranjang
                </button>
            </div>

            <div class="cart-items" id="cartItems">
               
            </div>

            <div class="empty-cart" id="emptyCart" style="display: none;">
                <div class="empty-cart-icon"><img src="assets/icons/ic_cart_empty.svg"/></div>
                <h2>Keranjang Belanja Kosong</h2>
                <p>Anda belum menambahkan produk apapun ke keranjang</p>
                <button class="checkout-btn" onclick="window.location.href='products.php'">
                    Belanja Sekarang
                </button>
            </div>
        </div>

        <div class="cart-summary">
            <h2>Ringkasan Pesanan</h2>
            
            <div class="summary-items" id="summaryItems">
    
            </div>

            <div class="summary-divider"></div>

            <div class="summary-total">
                <span>Subtotal</span>
                <span class="total-price" id="totalPrice">Rp 0</span>
            </div>

            <p class="shipping-note">Biaya pengiriman akan dihitung pada langkah berikutnya</p>

            <button class="checkout-btn" onclick="checkout()">
                Lanjutkan ke Checkout
            </button>

            <button class="continue-shopping-btn" onclick="window.location.href='products.php'">
                Lanjut Belanja
            </button>
        </div>
    </div>

    <script src="js/cart.js"></script>

<?php include 'includes/footer.php'; ?>