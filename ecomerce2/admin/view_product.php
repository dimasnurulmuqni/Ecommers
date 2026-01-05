<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/product_functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirectToLogin();
}

$product_id = $_GET['id'] ?? null;
$product = null;
if ($product_id) {
    $product = getProductById($product_id);
}

if (!$product) {
    header("Location: products.php?status=error&message=Produk tidak ditemukan.");
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #600000;
            --navy: #1a2a6c;
            --white: #ffffff;
            --bg: #f8fafc;
            --border: #e2e8f0;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); }

        .admin-layout { display: flex; min-height: 100vh; }

        .admin-sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--maroon) 0%, #5a0000 100%);
            color: white;
            position: fixed;
            height: 100vh;
            padding: 2rem 0;
            z-index: 100;
        }

        .admin-sidebar h3 {
            text-align: center;
            font-size: 1.4rem;
            margin-bottom: 2rem;
            color: var(--white);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 1rem;
        }

        .admin-sidebar ul { list-style: none; }

        .admin-sidebar ul li a {
            display: block;
            padding: 1rem 2rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .admin-sidebar ul li a:hover, .admin-sidebar ul li a.active {
            background-color: rgba(255,255,255,0.15);
            color: white;
            border-left: 4px solid var(--navy);
        }

        .nav-link {
            display: flex; align-items: center; gap: 1rem; padding: 0.8rem 2rem;
            color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s;
            font-size: 0.9rem;
        }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--navy); }

        .main-content { margin-left: 260px; flex: 1; padding: 2.5rem; }
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .page-header h1 { color: var(--navy); font-size: 1.8rem; font-weight: 700; }

        .detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: 400px 1fr;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .gallery-section {
            background: #f1f5f9;
            padding: 1.5rem;
            border-right: 1px solid var(--border);
        }
        .main-image-container {
            width: 100%;
            aspect-ratio: 1;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        .main-image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }
        .thumb-item {
            aspect-ratio: 1;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
            overflow: hidden;
            background: white;
        }
        .thumb-item:hover, .thumb-item.active { border-color: var(--maroon); }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

        .info-section { padding: 2.5rem; position: relative; }
        .category-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(26, 42, 108, 0.1);
            color: var(--navy);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }
        .product-title { font-size: 2.2rem; color: var(--text-main); margin-bottom: 0.5rem; }
        .product-price { font-size: 1.8rem; color: var(--maroon); font-weight: 700; margin-bottom: 2rem; }
        
        .spec-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 12px;
        }
        .spec-item label { display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 4px; font-weight: 500; }
        .spec-item p { font-weight: 600; color: var(--text-main); }

        .description-box { margin-bottom: 2.5rem; }
        .description-box h3 { font-size: 1rem; margin-bottom: 0.8rem; color: var(--navy); border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; }
        .description-text { line-height: 1.6; color: #475569; white-space: pre-wrap; }

        .action-group {
            display: flex;
            gap: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
        .btn {
            padding: 0.8rem 1.8rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        .btn-edit { background: var(--maroon); color: white; }
        .btn-edit:hover { background: var(--maroon-dark); transform: translateY(-2px); }
        .btn-back { background: #e2e8f0; color: var(--text-main); }
        .btn-back:hover { background: #cbd5e1; }

        @media (max-width: 1100px) {
            .detail-card { grid-template-columns: 1fr; }
            .gallery-section { border-right: none; border-bottom: 1px solid var(--border); }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>ADMIN PANEL</h3>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> &nbsp; Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> &nbsp; Manajemen Pesanan</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> &nbsp; Manajemen Produk</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> &nbsp; Manajemen Kategori</a></li>
                <li style="margin-top: 30px;"><a href="../logout.php" style="color: #ffbaba;"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Detail Produk</h1>
                <a href="products.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <div class="detail-card">
                <div class="gallery-section">
                    <div class="main-image-container" id="mainDisplay">
                        <img src="<?php echo htmlspecialchars(BASE_URL_PATH . ($product['images'][0] ?? 'assets/images/placeholder.jpg')); ?>" alt="Product">
                    </div>
                    
                    <div class="thumbnail-grid">
                        <?php 
                        if (isset($product['images']) && is_array($product['images'])):
                            foreach ($product['images'] as $index => $img_path):
                        ?>
                            <div class="thumb-item <?php echo ($index === 0) ? 'active' : ''; ?>" onclick="changeImage(this, '<?php echo htmlspecialchars(BASE_URL_PATH . $img_path); ?>')">
                                <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $img_path); ?>">
                            </div>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <div class="thumb-item active">
                                <img src="<?php echo htmlspecialchars(BASE_URL_PATH . 'assets/images/placeholder.jpg'); ?>">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-section">
                    <span class="category-badge"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <h2 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>

                    <div class="spec-grid">
                        <div class="spec-item">
                            <label>ID Produk</label>
                            <p>#<?php echo htmlspecialchars($product['id']); ?></p>
                        </div>
                        <div class="spec-item">
                            <label>Stok Tersedia</label>
                            <p><?php echo htmlspecialchars($product['stock']); ?> Unit</p>
                        </div>
                    </div>

                    <div class="description-box">
                        <h3>Deskripsi Produk</h3>
                        <div class="description-text"><?php echo htmlspecialchars($product['description']); ?></div>
                    </div>

                    <div class="action-group">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">
                            <i class="fas fa-edit"></i> Edit Informasi Produk
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function changeImage(element, src) {

            document.querySelector('#mainDisplay img').src = src;

            document.querySelectorAll('.thumb-item').forEach(item => {
                item.classList.remove('active');
            });
            element.classList.add('active');
        }
    </script>
</body>
</html>