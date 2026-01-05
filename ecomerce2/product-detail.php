<?php
include 'includes/header.php';
include_once 'includes/product_functions.php'; 

$product = null;
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($product_id) {
    $product = getProductById($product_id); 
}

if (!$product) {
    echo "<div class='container'><p>Produk tidak ditemukan.</p></div>";
    include 'includes/footer.php';
    exit();
}

$gallery = $product['images'] ?? ['assets/images/placeholder.jpg'];
?>

<div class="back-button">
    <a href="products.php" class="back-btn">← Kembali ke Produk</a>
</div>

<section class="product-detail">
    <div class="product-image-section">
        <div class="main-image-container">
            <img id="mainProductImage" class="product-main-image" src="<?php echo htmlspecialchars(BASE_URL_PATH . $gallery[0]); ?>" alt="Main Product">
        </div>
        
        <div class="product-thumbnails">
            <?php foreach ($gallery as $index => $img): ?>
                <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeImage(this, '<?php echo htmlspecialchars(BASE_URL_PATH . $img); ?>')">
                    <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $img); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="product-info-section">
        <span class="category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>

        <div class="product-description">
            <h3>Deskripsi Produk</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>

        <div class="stock-info">
            <img src="assets/icons/ic_box.svg"/> Stok: <span><?php echo htmlspecialchars($product['stock']); ?> unit</span>
        </div>

        <div class="quantity-section">
            <h3>Jumlah</h3>
            <div class="quantity-control">
                <button class="qty-btn" onclick="updateQty(-1)">−</button>
                <input type="number" class="qty-input" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                <button class="qty-btn" onclick="updateQty(1)">+</button>
                <span class="qty-max">Maks. <?php echo $product['stock']; ?> unit</span>
            </div>
        </div>
        
        <div class="product-actions">
            <button class="add-to-cart-btn" onclick="addToCart(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
            </button>
            <button class="buy-now-btn" onclick="buyNow(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                Beli Sekarang
            </button>
        </div>
        
        <div class="features-section2">
            <div class="feature-box2">
                <img class="feature-icon2" src="assets/icons/ic_good_product.svg"/>
                <div class="feature-text2">
                    <h4>Original</h4>
                    <p>Garansi keaslian</p>
                </div>
            </div>
            <div class="feature-box2">
                <img class="feature-icon2" src="assets/icons/ic_shipping.svg"/>
                <div class="feature-text2">
                    <h4>Gratis Ongkir</h4>
                    <p>Min. belanja 50rb</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>



function changeImage(element, src) {
    document.getElementById('mainProductImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}

function updateQty(change) {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    let newVal = parseInt(input.value) + change;

    if (newVal >= 1 && newVal <= max) {

        input.value = newVal;

    }
}


function addToCart(productData) {
    const qtyInput = document.getElementById('quantity');
    const quantity = parseInt(qtyInput.value);
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let existingItem = cart.find(item => item.id === productData.id);

    const mainImage = productData.images && productData.images.length > 0 ? productData.images[0] : 'assets/images/placeholder.jpg';

    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: productData.id,
            name: productData.name,
            price: productData.price,
            image: mainImage,
            quantity: quantity
        });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    alert(`${quantity} ${productData.name} ditambahkan ke keranjang!`);
    updateCartCount();
}

function buyNow(productData) {
    const qtyInput = document.getElementById('quantity');
    const quantity = parseInt(qtyInput.value);
    const mainImage = productData.images && productData.images.length > 0 ? productData.images[0] : 'assets/images/placeholder.jpg';

    const buyNowItem = {
        id: productData.id,
        name: productData.name,
        price: productData.price,
        image: mainImage,
        category: productData.category_name,
        quantity: quantity
    };

    localStorage.setItem('buyNowOrder', JSON.stringify([buyNowItem]));

    window.location.href = 'checkout.php'; 
}


function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartIconElement = document.getElementById('cartCountNav');
    if (cartIconElement) {
        cartIconElement.textContent = cartCount;
    }
}


document.addEventListener('DOMContentLoaded', updateCartCount);
</script>

    <script src="js/cart.js"></script>
<?php include 'includes/footer.php'; ?>
