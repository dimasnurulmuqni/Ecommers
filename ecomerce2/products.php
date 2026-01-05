<?php
include 'includes/header.php';
include_once 'includes/product_functions.php';

$categories = getCategories();

$search_query = $_GET['search'] ?? null;
$category_filter_name = $_GET['category'] ?? null;
$sort_by = $_GET['sort'] ?? 'name'; 

$category_filter_id = null;
if ($category_filter_name) {
    $found_category = db_getByCriteria('categories', ['name' => $category_filter_name]);
    if ($found_category) {
        $category_filter_id = $found_category['id'];
    }
}

$order_direction = 'ASC';
$order_column = 'name'; 

switch ($sort_by) {
    case 'newest':
        $order_column = 'id'; 
        $order_direction = 'DESC';
        break;
    case 'price-low':
        $order_column = 'price';
        $order_direction = 'ASC';
        break;
    case 'price-high':
        $order_column = 'price';
        $order_direction = 'DESC';
        break;
    case 'name':
    default:
        $order_column = 'name';
        $order_direction = 'ASC';
        break;
}

$products = getProducts($search_query, $category_filter_id, $order_column, $order_direction);

?>

    <div class="products-page">
        <div class="page-header">
            <h1>Semua Produk</h1>
            <p id="productCount">Menampilkan <?php echo count($products); ?> produk</p>
        </div>

        <div class="products-layout">
            
            <aside class="sidebar-filter">
                <form action="products.php" method="GET">
                    <div class="filter-section">
                        <h3>Filter Produk</h3>
                    </div>

                    <div class="filter-section">
                        <h3>Cari Produk</h3>
                        <div class="search-wrapper">
                            <input type="text" name="search" class="filter-search" placeholder="Nama produk..." value="<?php echo htmlspecialchars($search_query ?? ''); ?>">
                            <button type="submit" class="filter-search-btn">
                                <img src="assets/icons/ic_search.svg"/>
                            </button>
                        </div>
                    </div>

                    <div class="filter-section">
                        <h3>Kategori</h3>
                        <select name="category" class="filter-select">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php echo ($category_filter_name == $category['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-section">
                        <h3>Urutkan</h3>
                        <select name="sort" class="filter-select">
                            <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="price-low" <?php echo ($sort_by == 'price-low') ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="price-high" <?php echo ($sort_by == 'price-high') ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="name" <?php echo ($sort_by == 'name') ? 'selected' : ''; ?>>Nama A-Z</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter-apply">Terapkan Filter</button>
                </form>
            </aside>

            <!-- Products Grid -->
            <div>
                <div class="products-grid" id="productsGrid">
                    <?php if (empty($products)): ?>
                        <p>Tidak ada produk yang ditemukan.</p>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php 
                                $main_image_path = !empty($product['images'][0]) ? $product['images'][0] : 'assets/images/placeholder.jpg';
                            ?>
                            <div class="product-card" onclick="location.href='product-detail.php?id=<?php echo htmlspecialchars($product['id']); ?>'">
                                <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $main_image_path); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-card-image">
                                <div class="product-card-info">
                                    <div class="product-card-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                    <div class="product-card-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-card-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                                    <button class="add-to-cart" onclick="event.stopPropagation(); addToCart(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        <img src="assets/icons/ic_cart_white.svg"/>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function addToCart(product) {
            console.log("Produk ditambahkan:", product);

            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            let existing = cart.find(item => item.id == product.id);

            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.images?.[0] ?? '',
                    category: product.category_name, // Tambahkan kategori
                    quantity: 1
                });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();  // Ini akan berfungsi setelah diperbaiki
        }
    </script>
    
    <script src="js/cart.js"></script>

<?php include 'includes/footer.php'; ?>

    

