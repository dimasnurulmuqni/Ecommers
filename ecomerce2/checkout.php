<?php 
include 'includes/header.php';
require_once 'includes/db_connection.php';
require_once 'includes/db_functions.php';
require_once 'includes/product_functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $shipping_method = trim($_POST['shipping_method'] ?? '');
    $shipping_cost = (float)($_POST['shipping_cost'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');
    $cart_items_json = $_POST['cart_items'] ?? '[]';
    $note = trim($_POST['note'] ?? null);

    $cart_items = json_decode($cart_items_json, true);
    $upload_path = null; 

    if (empty($full_name) || empty($phone) || empty($address) || empty($city) || empty($postal_code) || empty($shipping_method) || empty($payment_method) || empty($cart_items)) {
        $errors[] = "Semua kolom pada langkah 2 dan 3 wajib diisi.";
    }

    if ($payment_method === 'transfer' && isset($_FILES['upload']) && $_FILES['upload']['error'] === UPLOAD_ERR_OK) { // Ganti 'payment_proof' menjadi 'upload'
        $upload_dir = 'uploads/proof/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_info = pathinfo($_FILES['upload']['name']);
        $file_ext = strtolower($file_info['extension']);
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed_exts)) {
            $unique_name = 'proof_' . uniqid() . '.' . $file_ext;
            $target_file = $upload_dir . $unique_name;
            if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_file)) { 
                $upload_path = $target_file;
            } else {
                $errors[] = "Gagal memindahkan file yang diunggah.";
            }
        } else {
            $errors[] = "Format file bukti pembayaran tidak valid. Hanya JPG, JPEG, PNG, GIF yang diizinkan.";
        }
    } elseif ($payment_method === 'transfer' && empty($_FILES['upload']['name'])) { 
        $errors[] = "Bukti pembayaran wajib diunggah untuk metode Transfer Bank.";
    }
    // ---------------------------

    $total_order_amount = $shipping_cost;
    $order_products = [];

    if (empty($errors)) {
        $_SESSION['invalid_product_ids'] = [];
        foreach ($cart_items as $item) {
            $product_db = getProductById($item['id']);
            if (!$product_db) {
                $_SESSION['invalid_product_ids'][] = $item['id'];
                continue; 
            }
            if ($item['quantity'] <= 0) { 
                $errors[] = "Kuantitas produk '{$item['name']}' tidak valid."; 
                break; 
            }
            if ($product_db['stock'] < $item['quantity']) { 
                $errors[] = "Stok untuk '{$item['name']}' tidak mencukupi."; 
                break; 
            }
            
            $item['price'] = $product_db['price'];
            $total_order_amount += ($item['price'] * $item['quantity']);
            $order_products[] = $item;
        }

        if (!empty($_SESSION['invalid_product_ids'])) {
            $_SESSION['checkout_error'] = 'Beberapa produk di keranjang Anda tidak lagi tersedia dan telah dihapus secara otomatis.';
            header('Location: cart.php');
            exit();
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $order_id = generateUniqueId('ORD');
            $user_id = $_SESSION['user_id'];
            $order_date = date('Y-m-d H:i:s');
            $shipping_address = "$full_name, $address, $city, $postal_code, Telp: $phone";

            db_insert('orders', [
                'id' => $order_id,
                'user_id' => $user_id,
                'order_date' => $order_date,
                'total_amount' => $total_order_amount,
                'shipping_cost' => $shipping_cost,
                'status' => 'pending',
                'shipping_address' => $shipping_address,
                'shipping_method' => $shipping_method,
                'payment_method' => $payment_method,
                'note' => $note,
                'upload' => $upload_path 
            ]);

            foreach ($order_products as $item) {
                db_insert('order_items', [
                    'order_id' => $order_id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price_at_order' => $item['price'],
                    'quantity' => $item['quantity']
                ]);
                db_update('products', $item['id'], ['stock' => db_getById('products', $item['id'])['stock'] - $item['quantity']]);
            }

            $pdo->commit();

            $source = $_POST['checkout_source'] ?? 'cart';
            header("Location: orders.php?order_success=true&order_id=" . $order_id . "&source=" . $source);
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Terjadi kesalahan saat memproses pesanan: " . $e->getMessage();
            error_log("Checkout error: " . $e->getMessage());
        }
    }
}
?>
    <div class="back-button">
        <a href="cart.php" class="back-btn">
            Kembali ke Keranjang
        </a>
    </div>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Checkout</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="message error" style="margin-bottom: 20px; padding: 15px; background: #ffebee; color: #d32f2f; border-radius: 8px;">
                <p><strong>Terjadi kesalahan saat memproses pesanan Anda:</strong></p>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="progress-steps">
            <div class="progress-line" id="progressLine"></div>
            <div class="step active" id="stepIndicator1">
                <div class="step-circle">1</div>
                <div class="step-label">Catatan</div>
            </div>
            <div class="step" id="stepIndicator2">
                <div class="step-circle">2</div>
                <div class="step-label">Alamat</div>
            </div>
            <div class="step" id="stepIndicator3">
                <div class="step-circle">3</div>
                <div class="step-label">Pengiriman</div>
            </div>
            <div class="step" id="stepIndicator4">
                <div class="step-circle">4</div>
                <div class="step-label">Pembayaran</div>
            </div>
        </div>

        <div class="checkout-layout">
            <div class="form-section" id="formSection">
                <form id="checkoutForm" method="POST" enctype="multipart/form-data">
                    <!-- Step 1 -->
                    <div id="step1Content" class="checkout-step-content">
                        <h2>Tambahkan Catatan (Opsional)</h2>
                        <div class="form-group">
                            <label>Catatan untuk Penjual</label>
                            <textarea class="form-input" name="note" id="note" placeholder="Misal: warna, ukuran, atau permintaan khusus lainnya..."></textarea>
                        </div>
                        <button type="button" class="submit-btn" onclick="goToStep(2)">Lanjutkan ke Alamat</button>
                    </div>

                    <!-- Step 2 -->
                    <div id="step2Content" class="checkout-step-content hidden">
                        <h2>Alamat Pengiriman</h2>
                        <div class="form-group">
                            <label>Nama Lengkap <span>*</span></label>
                            <input type="text" class="form-input" name="full_name" id="fullName" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon <span>*</span></label>
                            <input type="tel" class="form-input" name="phone" id="phone" placeholder="08xxxxxxxxxx" required>
                        </div>
                        <div class="form-group">
                            <label>Alamat Lengkap <span>*</span></label>
                            <textarea class="form-input" name="address" id="address" placeholder="Jalan, Nomor Rumah, RT/RW" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kota <span>*</span></label>
                                <input type="text" class="form-input" name="city" id="city" placeholder="Nama kota" required>
                            </div>
                            <div class="form-group">
                                <label>Kode Pos <span>*</span></label>
                                <input type="text" class="form-input" name="postal_code" id="postalCode" placeholder="12345" required>
                            </div>
                        </div>
                        <button type="button" class="submit-btn back-btn" style="background-color: #6c757d; margin-top: 1rem;" onclick="goToStep(1)">Kembali ke Catatan</button>
                        <button type="button" class="submit-btn" onclick="validateStep2()">Lanjutkan ke Pengiriman</button>
                    </div>

                    <!-- Step 3 -->
                    <div id="step3Content" class="checkout-step-content hidden">
                         <h2>Metode Pengiriman</h2>
                         <div class="option-group" id="shippingOptions">
                            <div class="option-card" data-shipping-method="jne-regular" data-shipping-cost="25000">
                                <div class="option-radio"></div><div class="option-content"><div class="option-title">JNE Regular</div><div class="option-subtitle">Estimasi: 3-4 hari</div></div><div class="option-price">Rp 25.000</div>
                            </div>
                             <div class="option-card" data-shipping-method="jnt-regular" data-shipping-cost="22000">
                                <div class="option-radio"></div><div class="option-content"><div class="option-title">J&T Regular</div><div class="option-subtitle">Estimasi: 3-4 hari</div></div><div class="option-price">Rp 22.000</div>
                            </div>
                        </div>
                        <input type="hidden" name="shipping_method" id="shipping_method">
                        <input type="hidden" name="shipping_cost" id="shipping_cost">
                        <button type="button" class="submit-btn back-btn" style="background-color: #6c757d; margin-top: 1rem;" onclick="goToStep(2)">Kembali ke Alamat</button>
                        <button type="button" class="submit-btn" onclick="goToStep(4)">Lanjutkan ke Pembayaran</button>
                    </div>
                    
                    <!-- Step 4 -->
                    <div id="step4Content" class="checkout-step-content hidden">
                        <h2 style="margin-top: 2rem;">Metode Pembayaran</h2>
                        <div class="option-group" id="paymentOptions">
                           <div class="option-card" data-payment-method="transfer">
                                <div class="option-radio"></div><div class="option-content"><div class="option-title">Transfer Bank</div><div class="option-subtitle">BCA, Mandiri, BRI, BNI</div></div>
                            </div>
                        </div>
                        
                        <div id="payment-instruction" class="hidden" style="margin-top: 20px; padding: 15px; background: #e7f3fe; border-radius: 8px;">
                            <h4>Silakan Transfer ke:</h4>
                            <p><strong>BCA:</strong> 123-456-7890 a/n John Doe</p>
                            <p><strong>Mandiri:</strong> 098-765-4321 a/n John Doe</p>
                            <div class="form-group" style="margin-top: 15px;">
                                <label for="upload">Upload Bukti Pembayaran <span>*</span></label>
                                <input type="file" name="upload" id="upload" class="form-input">
                            </div>
                        </div>
                        
                        <input type="hidden" name="payment_method" id="payment_method">
                        <input type="hidden" name="cart_items" id="cart_items">
                        
                        <button type="button" class="submit-btn back-btn" style="background-color: #6c757d; margin-top: 1rem;" onclick="goToStep(3)">Kembali ke Pengiriman</button>
                        <button type="submit" class="submit-btn" id="completeOrderBtn" style="margin-top: 1rem;">Selesaikan Pesanan</button>
                    </div>
                </form>
            </div>

            <div class="order-summary">
                <h2>Ringkasan Pesanan</h2>
                
                <div class="order-items" id="orderItems">
                 
                </div>

                <div class="summary-divider"></div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotalPrice">Rp 0</span>
                </div>

                <div class="summary-row">
                    <span>Biaya Pengiriman</span>
                    <span id="shippingPrice">Rp 0</span>
                </div>

                <div class="summary-divider"></div>

                <div class="summary-total">
                    <span>Total</span>
                    <span class="total-price" id="totalPrice">Rp 0</span>
                </div>
            </div>
        </div>
    </div>

    <script src="js/checkout.js"></script>

<?php include 'includes/footer.php'; ?>