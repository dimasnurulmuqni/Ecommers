<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirectToLogin();
}

$categories = getCategories(); 
$errors = [];
$success_message = '';

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    if (db_delete('categories', $delete_id)) {
        header("Location: categories.php?status=deleted");
        exit();
    } else {
        $errors[] = "Gagal menghapus kategori.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name'])) {
    $new_category_name = trim($_POST['category_name']);

    if (empty($new_category_name)) {
        $errors[] = "Nama kategori tidak boleh kosong.";
    } else {
        $existing_category = db_getByCriteria('categories', ['name' => $new_category_name]);
        
        if ($existing_category) {
            $errors[] = "Kategori sudah ada.";
        } else {
            $new_category_id = generateUniqueId('CAT'); 
            $new_category_data = [
                'id' => $new_category_id,
                'name' => $new_category_name
            ];
            if (db_insert('categories', $new_category_data)) {
                header("Location: categories.php?status=success");
                exit();
            } else {
                $errors[] = "Gagal menyimpan data ke database.";
            }
        }
    }
}

$status = $_GET['status'] ?? '';
if ($status === 'success') $success_message = "Kategori berhasil ditambahkan.";
if ($status === 'deleted') $success_message = "Kategori berhasil dihapus.";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #600000;
            --navy: #1a2a6c;
            --bg: #f8fafc;
            --white: #ffffff;
            --text-main: #1e293b;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: var(--text-main); }

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

        .nav-link {
            display: flex; align-items: center; gap: 1rem; padding: 0.8rem 2rem;
            color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--navy); }

        .main-content { margin-left: 260px; flex: 1; padding: 2.5rem; }
        .header-section { margin-bottom: 2rem; }
        .header-section h1 { color: var(--navy); font-size: 1.8rem; font-weight: 700; }

        .category-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem; align-items: start; }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border: 1px solid var(--border);
        }
        .card h2 { font-size: 1.25rem; margin-bottom: 1.5rem; color: var(--navy); display: flex; align-items: center; gap: 10px; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; }
        .form-input {
            width: 100%; padding: 0.75rem; border: 1px solid var(--border);
            border-radius: 8px; font-size: 1rem; transition: 0.3s;
        }
        .form-input:focus { outline: none; border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(128,0,0,0.1); }
        
        .submit-btn {
            width: 100%; padding: 0.75rem; background: var(--maroon); color: white;
            border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .submit-btn:hover { background: var(--maroon-dark); transform: translateY(-1px); }

        .category-table { width: 100%; border-collapse: collapse; }
        .category-table th { text-align: left; padding: 1rem; background: #f1f5f9; color: var(--text-main); font-size: 0.85rem; }
        .category-table td { padding: 1rem; border-bottom: 1px solid var(--border); }
        .btn-delete {
            color: #ef4444; text-decoration: none; font-size: 0.9rem;
            padding: 5px 10px; border-radius: 6px; transition: 0.3s;
        }
        .btn-delete:hover { background: #fee2e2; }

        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

        @media (max-width: 1024px) {
            .category-grid { grid-template-columns: 1fr; }
            .sidebar { width: 80px; }
            .sidebar-brand h2, .nav-link span { display: none; }
            .main-content { margin-left: 80px; }
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
                <li><a href="products.php" ><i class="fas fa-box"></i> &nbsp; Manajemen Produk</a></li>
                <li><a href="categories.php" class="active"><i class="fas fa-tags"></i> &nbsp;Manajemen Kategori</a></li>
                <li style="margin-top: 30px;"><a href="../logout.php" style="color: #ffbaba;"><i class="fas fa-sign-out-alt"></i> &nbsp; Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header-section">
                <h1>Manajemen Kategori</h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Kelola klasifikasi produk Anda di sini.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error) echo "<p><i class='fas fa-exclamation-circle'></i> $error</p>"; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <div class="category-grid">
                <div class="card">
                    <h2><i class="fas fa-plus-square"></i> Tambah Baru</h2>
                    <form action="categories.php" method="POST">
                        <div class="form-group">
                            <label>Nama Kategori</label>
                            <input type="text" name="category_name" class="form-input" placeholder="Contoh: Elektronik" required autofocus>
                        </div>
                        <button type="submit" class="submit-btn">Simpan Kategori</button>
                    </form>
                </div>

                <div class="card">
                    <h2><i class="fas fa-list"></i> Daftar Kategori</h2>
                    <?php if (empty($categories)): ?>
                        <div style="text-align: center; padding: 2rem; color: #94a3b8;">
                            <i class="fas fa-folder-open fa-3x" style="margin-bottom: 1rem;"></i>
                            <p>Belum ada kategori yang dibuat.</p>
                        </div>
                    <?php else: ?>
                        <table class="category-table">
                            <thead>
                                <tr>
                                    <th>Nama Kategori</th>
                                    <th style="width: 100px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                        <td style="text-align: center;">
                                            <a href="categories.php?delete=<?php echo $category['id']; ?>" 
                                               class="btn-delete" 
                                               onclick="return confirm('Hapus kategori ini? Produk dengan kategori ini mungkin perlu disesuaikan.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>