<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/product_functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirectToLogin();
}

$categories = getCategories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_product') {
    $name = trim($_POST['name'] ?? '');
    $category_name_from_form = trim($_POST['category'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    $uploadedImages = [];
    $uploadDir = __DIR__ . '/../uploads/products/';

    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            $errors[] = "Gagal membuat folder upload. Periksa permission folder.";
        }
    }

    if (isset($_FILES['images'])) {
        error_log("Files uploaded: " . print_r($_FILES['images'], true));
    }

    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $fileCount = count(array_filter($_FILES['images']['name']));
        if ($fileCount > 6) {
            $errors[] = "Maksimal hanya boleh mengunggah 6 gambar.";
        }
    }

    if (empty($errors) && isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        foreach ($_FILES['images']['name'] as $key => $filename) {
            if (empty($filename)) continue;

            if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi upload_max_filesize)',
                    UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi MAX_FILE_SIZE)',
                    UPLOAD_ERR_PARTIAL => 'File hanya ter-upload sebagian',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang di-upload',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
                    UPLOAD_ERR_EXTENSION => 'Upload dibatalkan oleh ekstensi PHP'
                ];
                $errorMsg = $uploadErrors[$_FILES['images']['error'][$key]] ?? 'Error tidak diketahui';
                error_log("Upload error untuk file $filename: $errorMsg");
                $errors[] = "Error upload $filename: $errorMsg";
                continue;
            }

            if (count($uploadedImages) >= 6) {
                break;
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($ext, $allowed)) {
                $errors[] = "Format file $filename tidak didukung. Gunakan: JPG, PNG, GIF, atau WEBP";
                continue;
            }

            $maxSize = 5 * 1024 * 1024;
            if ($_FILES['images']['size'][$key] > $maxSize) {
                $errors[] = "File $filename terlalu besar (max 5MB)";
                continue;
            }
            
            $newFilename = uniqid('prod_') . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $newFilename;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetPath)) {
                $uploadedImages[] = 'uploads/products/' . $newFilename;
                error_log("File berhasil di-upload: $newFilename");
            } else {
                error_log("Gagal memindahkan file $filename ke $targetPath");
                $errors[] = "Gagal mengunggah $filename. Periksa permission folder.";
            }
        }
    }

    if (empty($name)) $errors[] = "Nama produk wajib diisi.";
    if (empty($category_name_from_form)) $errors[] = "Kategori wajib dipilih.";
    if (empty($description)) $errors[] = "Deskripsi wajib diisi.";
    if ($price <= 0) $errors[] = "Harga harus lebih besar dari nol.";
    if ($stock < 0) $errors[] = "Stok tidak boleh negatif.";
    if (empty($uploadedImages)) {
        $errors[] = "Minimal harus ada 1 gambar produk.";
        error_log("Tidak ada gambar yang berhasil di-upload. UploadedImages: " . print_r($uploadedImages, true));
    }

    if (empty($errors)) {
        $category_obj = db_getByCriteria('categories', ['name' => $category_name_from_form]);
        if (!$category_obj) {
            $errors[] = "Kategori tidak ditemukan.";
        } else {
            $category_id = $category_obj['id'];

            $product_data = [
                'name' => $name,
                'category_id' => $category_id,
                'price' => $price,
                'stock' => $stock,
                'description' => $description
            ];
            
            if (addProduct($product_data, $uploadedImages)) {
                header("Location: products.php?status=success&message=Produk berhasil ditambahkan.");
                exit();
            } else {
                $errors[] = "Gagal menyimpan produk ke database.";
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
    <title>Tambah Produk - Admin</title>
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

        .main-content { margin-left: 260px; flex: 1; padding: 2rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { color: var(--navy); font-size: 1.8rem; }

        .form-container { 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
            overflow: hidden; 
        }
        
        .form-layout { 
            display: grid; 
            grid-template-columns: 1fr 380px; 
        }
        
        .form-section { padding: 2rem; }
        .preview-section { 
            background: #f1f5f9; 
            border-left: 1px solid var(--border); 
            padding: 2rem; 
        }

        .form-group { margin-bottom: 1.2rem; }
        .form-label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 0.5rem; 
            font-size: 0.9rem; 
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%; 
            padding: 0.75rem; 
            border: 1.5px solid var(--border); 
            border-radius: 8px; 
            transition: 0.3s;
            font-family: inherit;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus { 
            outline: none; 
            border-color: var(--maroon); 
            box-shadow: 0 0 0 3px rgba(128,0,0,0.1); 
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .upload-area {
            border: 2px dashed var(--maroon); 
            border-radius: 12px; 
            padding: 1.5rem;
            text-align: center; 
            cursor: pointer; 
            background: rgba(128,0,0,0.02); 
            transition: 0.3s;
        }
        
        .upload-area:hover { 
            background: rgba(128,0,0,0.05); 
        }
        
        .upload-area.disabled { 
            opacity: 0.5; 
            cursor: not-allowed; 
            border-color: #cbd5e1; 
        }

        .image-preview-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 10px; 
            margin-top: 1rem; 
        }
        
        .image-preview-item { 
            position: relative; 
            aspect-ratio: 1; 
            border-radius: 8px; 
            overflow: hidden; 
            border: 1px solid var(--border); 
        }
        
        .image-preview-item img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        
        .remove-image {
            position: absolute; 
            top: 5px; 
            right: 5px; 
            background: rgba(220,38,38,0.9);
            color: white; 
            border: none; 
            border-radius: 50%; 
            width: 24px; 
            height: 24px; 
            cursor: pointer; 
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .remove-image:hover {
            background: #dc2626;
        }

        .preview-card { 
            background: white; 
            border-radius: 12px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
        }
        
        .preview-main-img { 
            width: 100%; 
            height: 250px; 
            background: #e2e8f0; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden; 
        }
        
        .preview-main-img img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        
        .preview-info { padding: 1.5rem; }
        .preview-price { 
            color: var(--maroon); 
            font-weight: 700; 
            font-size: 1.25rem; 
        }

        .form-actions { 
            padding: 1.5rem 2rem; 
            border-top: 1px solid var(--border); 
            display: flex; 
            justify-content: flex-end; 
            gap: 1rem; 
        }
        
        .btn-submit { 
            background: var(--maroon); 
            color: white; 
            border: none; 
            padding: 0.8rem 2rem; 
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.3s;
        }
        
        .btn-submit:hover { 
            background: var(--maroon-dark); 
        }

        .alert { 
            background: #fee2e2; 
            color: #991b1b; 
            padding: 1rem; 
            border-radius: 8px; 
            margin-bottom: 1.5rem; 
            border: 1px solid #fecaca; 
        }

        .alert ul {
            list-style: none;
            padding: 0;
        }

        .alert li {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 1024px) { 
            .form-layout { grid-template-columns: 1fr; } 
            .preview-section { 
                border-left: none; 
                border-top: 1px solid var(--border); 
            } 
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
                <h1>Tambah Produk Baru</h1>
                <p>Kelola katalog produk toko Anda</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="add_product.php" method="POST" enctype="multipart/form-data" id="productForm">
                <input type="hidden" name="action" value="add_product">
                <div class="form-container">
                    <div class="form-layout">
                        <div class="form-section">
                            <div class="form-group">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" name="name" class="form-input" placeholder="Contoh: Baju Kemeja Putih" required oninput="updatePreview()" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <label class="form-label">Kategori</label>
                                    <select name="category" class="form-select" required onchange="updatePreview()">
                                        <option value="">Pilih...</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat['name']) ?>" <?= (isset($_POST['category']) && $_POST['category'] == $cat['name']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Harga (Rp)</label>
                                    <input type="number" name="price" class="form-input" placeholder="150000" required oninput="updatePreview()" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stock" class="form-input" placeholder="10" required oninput="updatePreview()" value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Gambar Produk (Maks 6)</label>
                                <div class="upload-area" id="uploadArea" onclick="triggerFileInput()">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--maroon);"></i>
                                    <p id="uploadText">Klik untuk unggah gambar (0/6)</p>
                                    <p style="font-size: 0.8rem; color: #64748b; margin-top: 0.5rem;">Mendukung: JPG, PNG, GIF, WEBP (Max 5MB per file)</p>
                                </div>
                                <input type="file" id="imageInput" name="images[]" accept="image/*" multiple style="display:none;" onchange="handleFileSelect(event)">
                                <div id="imagePreviewGrid" class="image-preview-grid"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-textarea" placeholder="Deskripsikan produk Anda..." required oninput="updatePreview()"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="preview-section">
                            <h3 style="margin-bottom:1rem; font-size:1rem; color:var(--navy);">Live Preview</h3>
                            <div class="preview-card">
                                <div class="preview-main-img" id="mainPreview">
                                    <i class="fas fa-image" style="font-size:3rem; color:#cbd5e1;"></i>
                                </div>
                                <div class="preview-info">
                                    <p id="pCategory" style="font-size:0.7rem; text-transform:uppercase; color:#64748b; font-weight:700;">KATEGORI</p>
                                    <h3 id="pName" style="margin: 5px 0;">Nama Produk</h3>
                                    <p id="pPrice" class="preview-price">Rp 0</p>
                                    <p id="pStock" style="font-size:0.8rem; color:#64748b; margin-top:5px;">Stok: 0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="products.php" style="text-decoration:none; color:#64748b; font-weight:600; padding:0.8rem;">Batal</a>
                        <button type="submit" class="btn-submit">Simpan Produk</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        let allFiles = []; 

        function triggerFileInput() {
            if(allFiles.length < 6) {
                document.getElementById('imageInput').click();
            } else {
                alert("Maksimal 6 gambar!");
            }
        }

        function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    const container = document.getElementById('imagePreviewGrid');
    container.innerHTML = '';

    if (files.length > 6) {
        alert("Maksimal 6 gambar!");
        event.target.value = '';
        return;
    }

            files.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}">
                    `;
                    container.appendChild(div);

                    if (index === 0) {
                        document.getElementById('mainPreview').innerHTML =
                            `<img src="${e.target.result}">`;
                    }
                };
                reader.readAsDataURL(file);
            });

            document.getElementById('uploadText').innerText =
                `Klik untuk unggah gambar (${files.length}/6)`;
        }


        function removeImage(index) {
            allFiles.splice(index, 1);
            updateFilesAndPreviews();
        }

        function updateFilesAndPreviews() {
            const container = document.getElementById('imagePreviewGrid');
            const uploadText = document.getElementById('uploadText');
            const uploadArea = document.getElementById('uploadArea');
            container.innerHTML = '';

            uploadText.innerText = `Klik untuk unggah gambar (${allFiles.length}/6)`;
            
            if(allFiles.length >= 6) {
                uploadArea.classList.add('disabled');
            } else {
                uploadArea.classList.remove('disabled');
            }

            const dataTransfer = new DataTransfer();
            allFiles.forEach((file, index) => {
                dataTransfer.items.add(file);

                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <button type="button" class="remove-image" onclick="removeImage(${index})">✕</button>
                    `;
                    container.appendChild(div);
                    
                    if(index === 0) {
                        document.getElementById('mainPreview').innerHTML = `<img src="${e.target.result}" alt="Main preview">`;
                    }
                };
                reader.readAsDataURL(file);
            });
            

            if(allFiles.length === 0) {
                document.getElementById('mainPreview').innerHTML = `<i class="fas fa-image" style="font-size:3rem; color:#cbd5e1;"></i>`;
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
        window.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</body>
</html>