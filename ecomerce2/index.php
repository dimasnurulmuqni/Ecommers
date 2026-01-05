<?php
include 'includes/header.php';
include_once 'includes/product_functions.php';

$products = getProducts(null, null, 'id', 'DESC'); 
$latest_products = array_slice($products, 0, 10); 
$categories = getCategories();
?>

    <section class="hero">
        <div class="hero-content">
            <h1>Belanja Online<br>Mudah & Terpercaya</h1>
            <p>Temukan berbagai produk berkualitas dengan harga terbaik. Pembayaran mudah dengan sistem upload bukti transfer.</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
                <a href="about.php" class="btn btn-secondary">Tentang Kami</a>
            </div>
        </div>
    </section>

    <div class="search-section">
        <input type="text" class="search-input" placeholder="Cari produk yang Anda inginkan..." id="searchInput">
        <select class="category-select" id="categoryFilter">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category['name']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-search" onclick="searchProducts()">Cari</button>
    </div>

    <section class="features">
        <div class="feature-card">
            <img class="feature-icon" src="assets/icons/ic_good_product.svg"/>
            <h3>Produk Berkualitas</h3>
            <p>Semua produk telah melalui seleksi ketat untuk kualitas terbaik</p>
        </div>
        <div class="feature-card">
            <img class="feature-icon" src="assets/icons/ic_nice_payment.svg"/>
            <h3>Pembayaran Aman</h3>
            <p>Sistem pembayaran dengan upload bukti transfer yang mudah</p>
        </div>
        <div class="feature-card">
            <img class="feature-icon" src="assets/icons/ic_shipping.svg"/>
            <h3>Pengiriman Cepat</h3>
            <p>Berbagai pilihan ekspedisi untuk pengiriman yang cepat</p>
        </div>
        <div class="feature-card">
            <img class="feature-icon" src="assets/icons/ic_percent.svg"/>
            <h3>Harga Terbaik</h3>
            <p>Dapatkan produk berkualitas dengan harga yang kompetitif</p>
        </div>
    </section>

    <section class="products-section">
        <div class="section-header">
            <div>
                <h2>Produk Terbaru</h2>
                <p><?php echo count($latest_products); ?> produk terbaru yang baru saja ditambahkan</p>
            </div>
            <a href="products.php" class="view-all">Lihat Semua</a>
        </div>
        <div class="products-grid" id="productsGrid">
            <?php if (empty($latest_products)): ?>
                <p>Tidak ada produk terbaru.</p>
            <?php else: ?>
                <?php foreach ($latest_products as $product): ?>
                    <?php 
                        $main_image_path = !empty($product['images'][0]) ? $product['images'][0] : 'assets/images/placeholder.jpg';
                    ?>
                    <div class="product-card" onclick="location.href='product-detail.php?id=<?php echo htmlspecialchars($product['id']); ?>'">
                        <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $main_image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-card-image">
                        <div class="product-card-info">
                            <div class="product-card-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-card-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <div class="product-card-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <script src="js/index.js"></script>

<?php include 'includes/footer.php'; ?>