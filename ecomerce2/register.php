<?php

$errors = [];
$success_message = '';

// Ensure header.php is included first to start session and include functions
include 'includes/header.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($username)) {
        $errors[] = "Username harus diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password harus minimal 6 karakter.";
    }

    if (empty($errors)) {
        // Check if username already exists in database
        $existing_user = getUserByUsername($username);

        if ($existing_user) {
            $errors[] = "Username sudah terdaftar.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $new_user_id = generateUniqueId('USER');
            $new_user_data = [
                'id' => $new_user_id,
                'username' => $username,
                'password' => $hashed_password,
                'role' => 'consumer' // Default role for registration
            ];
            
            if (db_insert('users', $new_user_data)) {
                // Redirect to login page with success message
                header("Location: login.php?registered=true");
                exit();
            } else {
                $errors[] = "Gagal menyimpan data pengguna. Coba lagi.";
            }
        }
    }
}
?>

<style>
    /* Specific styles for register/login form */
    .auth-container {
        max-width: 500px;
        margin: 5rem auto;
        padding: 2rem;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }

    .auth-container h2 {
        font-size: 2rem;
        color: #333;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        text-align: left;
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
        width: 100%;
        padding: 1rem;
        background-color: #8b1538;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
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

    .auth-link {
        margin-top: 1.5rem;
        font-size: 0.95rem;
        color: #666;
    }

    .auth-link a {
        color: #8b1538;
        text-decoration: none;
        font-weight: 600;
    }

    .auth-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="auth-container">
    <h2>Daftar Akun Baru</h2>
    <?php if (!empty($errors)): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="message success">
            <p><?php echo $success_message; ?></p>
        </div>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" class="form-input" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-input" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
        </div>
        <button type="submit" class="submit-btn">Daftar</button>
    </form>
    <p class="auth-link">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>

<?php include 'includes/footer.php'; ?>
