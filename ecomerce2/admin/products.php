<?php
session_start();
require_once __DIR__ . '/../includes/functions.php'; 
require_once __DIR__ . '/../includes/product_functions.php'; 

if (!isLoggedIn() || !isAdmin()) {
    redirectToLogin();
}

$categories = getCategories(); 
$errors = [];
$success_message = '';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $product_id_to_delete = $_GET['id'];
    if (deleteProduct($product_id_to_delete)) { 
        header("Location: products.php?status=success&message=Produk berhasil dihapus.");
        exit();
    } else {
        header("Location: products.php?status=error&message=Gagal menghapus produk.");
        exit();
    }
}

$search_query = $_GET['search'] ?? null;
$category_filter_name = $_GET['category'] ?? null;
$category_filter_id = null;

if ($category_filter_name) {
    $found_category = db_getByCriteria('categories', ['name' => $category_filter_name]);
    if ($found_category) {
        $category_filter_id = $found_category['id'];
    }
}

$filtered_products = getProducts($search_query, $category_filter_id, 'name', 'ASC'); 

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --maroon: #800000;
            --navy: #1a2a6c;
            --white: #ffffff;
            --bg-light: #f4f7f6;
            --text-dark: #333;
            --danger: #d9534f;
            --success: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-light);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

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

        .admin-sidebar ul {
            list-style: none;
        }

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

        .admin-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 2.5rem;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header-section h1 {
            color: var(--navy);
            font-size: 1.8rem;
        }

        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .form-input, .form-select {
            padding: 0.7rem 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .form-input { flex-grow: 2; }
        .form-select { flex-grow: 1; }

        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-maroon { background-color: var(--maroon); color: white; }
        .btn-maroon:hover { background-color: #600000; }
        
        .btn-navy { background-color: var(--navy); color: white; }
        .btn-navy:hover { background-color: #121d4d; }

        .btn-reset { background-color: #6c757d; color: white; }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
        }

        .product-table th {
            background-color: #f8f9fa;
            color: var(--navy);
            padding: 1.2rem 1rem;
            text-align: left;
            border-bottom: 2px solid #eee;
        }

        .product-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .product-table tr:hover { background-color: #fafafa; }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            text-decoration: none;
            color: white;
        }

        .view { background-color: #17a2b8; }
        .edit { background-color: var(--navy); }
        .delete { background-color: var(--danger); }

        .badge-stock {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            background: #e9ecef;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h3>ADMIN PANEL</h3>
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> &nbsp; Dashboard</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> &nbsp; Manajemen Pesanan</a></li>
                <li><a href="products.php" class="active"><i class="fas fa-box"></i> &nbsp; Manajemen Produk</a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i> &nbsp;Manajemen Kategori</a></li>
                <li style="margin-top: 30px;"><a href="../logout.php" style="color: #ffbaba;"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="header-section">
                <h1>Daftar Produk Toko</h1>
                <a href="add_product.php" class="btn btn-maroon">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
                </a>
            </div>

            <form action="products.php" method="GET" class="filter-card">
                <input type="text" name="search" placeholder="Cari nama atau deskripsi produk..." class="form-input" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                
                <select name="category" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo (($_GET['category'] ?? '') == $cat['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-navy">Filter</button>
                <a href="products.php" class="btn btn-reset">Reset</a>
            </form>

            <div class="table-container">
                <?php if (empty($filtered_products)): ?>
                    <div style="padding: 2rem; text-align: center; color: #666;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; color: #ddd;"></i>
                        <p>Oops! Tidak ada produk yang ditemukan.</p>
                    </div>
                <?php else: ?>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_products as $product): ?>
                                <?php 
                                    $product_images = getProductImages($product['id']);
                                    $main_image_path = !empty($product_images) ? $product_images[0] : 'assets/images/placeholder.jpg'; 
                                ?>
                                <tr>
                                    <td style="display: flex; align-items: center; gap: 15px;">
                                        <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $main_image_path); ?>" class="product-img">
                                        <span style="font-weight: 600; color: var(--navy);"><?php echo htmlspecialchars($product['name']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><strong style="color: var(--maroon);">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></strong></td>
                                    <td><span class="badge-stock"><?php echo htmlspecialchars($product['stock']); ?> Unit</span></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="view_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="action-btn view" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="action-btn edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="products.php?action=delete&id=<?php echo htmlspecialchars($product['id']); ?>" 
                                               class="action-btn delete" 
                                               onclick="return confirm('Hapus produk ini secara permanen?');" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>