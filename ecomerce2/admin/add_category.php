<?php
session_start();
include_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirectToLogin();
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');

    if (empty($category_name)) {
        $errors[] = "Nama kategori tidak boleh kosong.";
    }

    if (empty($errors)) {
        $categories = readJsonFile('data/categories.json');

        foreach ($categories as $cat) {
            if (strtolower($cat['name']) === strtolower($category_name)) {
                $errors[] = "Kategori dengan nama ini sudah ada.";
                break;
            }
        }

        if (empty($errors)) {
            $new_category = [
                'id' => 'cat_' . uniqid(),
                'name' => $category_name
            ];

            $categories[] = $new_category;

            if (writeJsonFile('data/categories.json', $categories)) {
                $success_message = "Kategori baru berhasil ditambahkan.";
                $_POST = [];
            } else {
                $errors[] = "Gagal menyimpan kategori baru.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .admin-sidebar {
            width: 250px;
            background-color: #333;
            color: white;
            padding: 2rem 0;
            flex-shrink: 0;
        }
        .admin-sidebar h3 {
            text-align: center;
            margin-bottom: 2rem;
            color: #8b1538;
        }
        .admin-sidebar ul {
            list-style: none;
            padding: 0;
        }
        .admin-sidebar ul li a {
            display: block;
            padding: 0.8rem 2rem;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .admin-sidebar ul li a:hover, .admin-sidebar ul li a.active {
            background-color: #8b1538;
        }
        .admin-content {
            flex-grow: 1;
            padding: 2rem;
            background-color: #f5f5f5;
        }
        .admin-content h1 {
            color: #333;
            margin-bottom: 2rem;
        }
        .form-section {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        .form-section h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-input:focus {
            outline: none;
            border-color: #8b1538;
        }
        .submit-btn {
            padding: 0.8rem 1.5rem;
            background-color: #8b1538;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .submit-btn:hover {
            background-color: #6d1129;
        }
        .message.error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .message.success {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <h3>Admin Panel</h3>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="orders.php">Manajemen Pesanan</a></li>
                <li><a href="products.php">Manajemen Produk</a></li>
                <li><a href="categories.php" class="active">Manajemen Kategori</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>
        <main class="admin-content">
            <h1>Tambah Kategori Baru</h1>

            <div class="form-section">
                <?php if (!empty($errors)): ?>
                    <div class="message error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <div class="message success">
                        <p><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                <?php endif; ?>
                <form action="add_category.php" method="POST">
                    <div class="form-group">
                        <label for="category_name">Nama Kategori:</label>
                        <input type="text" id="category_name" name="category_name" class="form-input" required value="<?php echo htmlspecialchars($_POST['category_name'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="submit-btn">Tambah Kategori</button>
                </form>
            </div>
        </main>
    </div>

</body>
</html>