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

$categories = getCategories();
$errors = [];
$success_message = '';

$display_name = $_POST['name'] ?? $product['name'];
$display_category_name = $_POST['category'] ?? $product['category_name']; 
$display_price = $_POST['price'] ?? $product['price'];
$display_stock = $_POST['stock'] ?? $product['stock'];
$display_description = $_POST['description'] ?? $product['description'];
$current_images = $product['images'] ?? []; 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_product') {
    $name = trim($_POST['name'] ?? '');
    $category_name_from_form = trim($_POST['category'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    $uploadedImages = [];
    $uploadDir = '../uploads/products/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        if (count($_FILES['images']['name']) > 6) {
            $errors[] = "Maksimal hanya boleh mengunggah 6 gambar.";
        }
        foreach ($_FILES['images']['name'] as $key => $filename) {
            if ($_FILES['images']['error'][$key] == 0 && $key < 6) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $newFilename = generateUniqueId() . '.' . $ext;
                    $targetPath = $uploadDir . $newFilename;
                    
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetPath)) {
                        $uploadedImages[] = 'uploads/products/' . $newFilename;
                    }
                }
            }
        }
    }

    $final_image_paths = !empty($uploadedImages) ? $uploadedImages : $current_images;

    if (empty($name) || empty($category_name_from_form) || empty($description)) {
        $errors[] = "Nama, Kategori, dan Deskripsi wajib diisi.";
    }
    if ($price <= 0) {
        $errors[] = "Harga harus lebih besar dari nol.";
    }
    if ($stock < 0) {
        $errors[] = "Stok tidak boleh negatif.";
    }
    if (empty($final_image_paths)) {
        $errors[] = "Minimal harus ada 1 gambar produk.";
    }

    if (empty($errors)) {
        $category_obj = db_getByCriteria('categories', ['name' => $category_name_from_form]);
        if (!$category_obj) {
            $errors[] = "Kategori tidak ditemukan.";
        } else {
            $category_id = $category_obj['id'];

            $product_data_to_update = [
                'name' => $name,
                'category_id' => $category_id,
                'price' => $price,
                'stock' => $stock,
                'description' => $description
            ];
            
            if (updateProduct($product_id, $product_data_to_update, $final_image_paths)) {
                $success_message = "Produk berhasil diperbarui.";
                $product = getProductById($product_id);
                $current_images = $product['images'] ?? [];
            } else {
                $errors[] = "Gagal memperbarui produk ke database.";
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
    <title>Edit Produk - Admin</title>
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
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg); color: #1e293b; }

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
        }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--navy); }

        .main-content { margin-left: 260px; flex: 1; padding: 2rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { color: var(--navy); font-size: 1.8rem; }

        .form-container { background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }
        .form-layout { display: grid; grid-template-columns: 1fr 380px; }
        .form-section { padding: 2rem; }
        .preview-section { background: #f1f5f9; border-left: 1px solid var(--border); padding: 2rem; }

        .form-group { margin-bottom: 1.2rem; }
        .form-label { display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-input, .form-select, .form-textarea {
            width: 100%; padding: 0.75rem; border: 1.5px solid var(--border); border-radius: 8px; transition: 0.3s;
        }
        .form-input:focus { outline: none; border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(128,0,0,0.1); }

        .upload-area {
            border: 2px dashed var(--maroon); border-radius: 12px; padding: 1.5rem;
            text-align: center; cursor: pointer; background: rgba(128,0,0,0.02); transition: 0.3s;
        }
        .upload-area:hover { background: rgba(128,0,0,0.05); }
        .upload-area.disabled { opacity: 0.5; cursor: not-allowed; border-color: #cbd5e1; }

        .image-preview-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 1rem; }
        .image-preview-item { position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid var(--border); }
        .image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .remove-image {
            position: absolute; top: 2px; right: 2px; background: rgba(220,38,38,0.9);
            color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 10px;
        }

        .preview-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .preview-main-img { width: 100%; height: 250px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .preview-main-img img { width: 100%; height: 100%; object-fit: cover; }
        .preview-info { padding: 1.5rem; }
        .preview-price { color: var(--maroon); font-weight: 700; font-size: 1.25rem; }

        .form-actions { padding: 1.5rem 2rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 1rem; }
        .btn-submit { background: var(--maroon); color: white; border: none; padding: 0.8rem 2rem; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: var(--maroon-dark); }
        
        .alert { background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fecaca; }

        @media (max-width: 1024px) { .form-layout { grid-template-columns: 1fr; } .preview-section { border-left: none; border-top: 1px solid var(--border); } }
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
                <h1>Edit Produk: <?php echo htmlspecialchars($product['name'] ?? ''); ?></h1>
                <p>Ubah detail produk ini</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <?php foreach ($errors as $e): ?> <p><i class="fas fa-exclamation-circle"></i> <?php echo $e; ?></p> <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">
                    <p><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>

            <form action="edit_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="action" value="edit_product">
                <div class="form-container">
                    <div class="form-layout">
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" name="name" class="form-input" required oninput="updatePreview()" value="<?php echo htmlspecialchars($display_name); ?>">
                            </div>
                            <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <label class="form-label">Kategori</label>
                                    <select name="category" class="form-select" required onchange="updatePreview()">
                                        <option value="">Pilih...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['name']) ?>" <?php echo ($display_category_name == $cat['name']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Harga (Rp)</label>
                                    <input type="number" name="price" class="form-input" required oninput="updatePreview()" value="<?php echo htmlspecialchars($display_price); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stock" class="form-input" required oninput="updatePreview()" value="<?php echo htmlspecialchars($display_stock); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Gambar Produk (Maks 6)</label>
                                <div class="upload-area" id="uploadArea" onclick="triggerFileInput()">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--maroon);"></i>
                                    <p id="uploadText">Klik untuk unggah (0/6)</p>
                                </div>
                                <input type="file" id="imageInput" name="images[]" accept="image/*" multiple style="display:none;" onchange="handleFileSelect(event)">
                                <div id="imagePreviewGrid" class="image-preview-grid"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-textarea" oninput="updatePreview()"><?php echo htmlspecialchars($display_description); ?></textarea>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h3 style="margin-bottom:1rem; font-size:1rem; color:var(--navy);">Live Preview</h3>
                            <div class="preview-card">
                                <div class="preview-main-img" id="mainPreview">
                                    <?php if (!empty($current_images[0])): ?>
                                        <img src="<?php echo htmlspecialchars(BASE_URL_PATH . $current_images[0]); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-image" style="font-size:3rem; color:#cbd5e1;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="preview-info">
                                    <p id="pCategory" style="font-size:0.7rem; text-transform:uppercase; color:#64748b; font-weight:700;"><?php echo htmlspecialchars($display_category_name); ?></p>
                                    <h3 id="pName" style="margin: 5px 0;"><?php echo htmlspecialchars($display_name); ?></h3>
                                    <p id="pPrice" class="preview-price">Rp <?php echo number_format($display_price, 0, ',', '.'); ?></p>
                                    <p id="pStock" style="font-size:0.8rem; color:#64748b; margin-top:5px;">Stok: <?php echo htmlspecialchars($display_stock); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a href="products.php" style="text-decoration:none; color:#64748b; font-weight:600; padding:0.8rem;">Batal</a>
                        <button type="submit" class="btn-submit">Update Produk</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        let allFiles = []; 
        let initialImages = <?php echo json_encode($current_images); ?>; 
        let initialImageElements = []; 
        function initializeFiles() {
            initialImages.forEach(imgPath => {
                initialImageElements.push({
                    name: imgPath.substring(imgPath.lastIndexOf('/') + 1),
                    path: imgPath
                });
            });
            updateFilesAndPreviews();
        }


        function triggerFileInput() {
            if((allFiles.length + initialImageElements.length) < 6) {
                document.getElementById('imageInput').click();
            } else {
                alert("Maksimal 6 gambar!");
            }
        }

        function handleFileSelect(event) {
            const files = Array.from(event.target.files);
            
            files.forEach(file => {
                if ((allFiles.length + initialImageElements.length) < 6) {
                    const isDuplicate = allFiles.some(f => f.name === file.name && f.size === file.size);
                    if(!isDuplicate) {
                        allFiles.push(file);
                    }
                }
            });

            updateFilesAndPreviews();
            event.target.value = ''; 
        }

        function removeImage(index, isInitial = false) {
            if (isInitial) {
                initialImageElements.splice(index, 1);
            } else {
                allFiles.splice(index, 1);
            }
            updateFilesAndPreviews();
        }

        function updateFilesAndPreviews() {
            const container = document.getElementById('imagePreviewGrid');
            const uploadText = document.getElementById('uploadText');
            const uploadArea = document.getElementById('uploadArea');
            container.innerHTML = '';

            const totalImages = allFiles.length + initialImageElements.length;

            uploadText.innerText = `Klik untuk unggah (${totalImages}/6)`;
            if(totalImages >= 6) {
                uploadArea.classList.add('disabled');
            } else {
                uploadArea.classList.remove('disabled');
            }

            initialImageElements.forEach((imgData, index) => {
                const div = document.createElement('div');
                div.className = 'image-preview-item';
                div.innerHTML = `
                    <img src="${BASE_URL_PATH}${imgData.path}">
                    <button type="button" class="remove-image" onclick="removeImage(${index}, true)"><i class="fas fa-times"></i></button>
                    <input type="hidden" name="existing_images[]" value="${imgData.path}">
                `;
                container.appendChild(div);
            });

            allFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}">
                        <button type="button" class="remove-image" onclick="removeImage(${index}, false)"><i class="fas fa-times"></i></button>
                    `;
                    container.appendChild(div);

                    if(initialImageElements.length === 0 && index === 0) {
                        document.getElementById('mainPreview').innerHTML = `<img src="${e.target.result}">`;
                    }
                };
                reader.readAsDataURL(file);
            });

            if(totalImages === 0) {
                document.getElementById('mainPreview').innerHTML = `<i class="fas fa-image" style="font-size:3rem; color:#cbd5e1;"></i>`;
            } else if (initialImageElements.length > 0 && totalImages > 0) {
                 document.getElementById('mainPreview').innerHTML = `<img src="${BASE_URL_PATH}${initialImageElements[0].path}">`;
            }
        }

        function updatePreview() {
            const name = document.querySelector('[name="name"]').value || "Nama Produk";
            const cat = document.querySelector('[name="category"]').value || "KATEGORI";
            const price = document.querySelector('[name="price"]').value || "0";
            const stock = document.querySelector('[name="stock"]').value || "0";

            document.getElementById('pName').innerText = name;
            document.getElementById('pCategory').innerText = cat;
            document.getElementById('pPrice').innerText = "Rp " + parseInt(price).toLocaleString('id-ID');
            document.getElementById('pStock').innerText = "Stok: " + stock;
        }

        document.addEventListener('DOMContentLoaded', initializeFiles);
    </script>
</body>
</html>